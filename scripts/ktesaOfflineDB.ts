/// <reference types="leaflet" />
interface IndexedReqResult extends EventTarget {
	result?: IDBDatabase;
	errorCode?: string;
}
interface IndexedDBError extends EventTarget {
	error: ErrMsg;
}
interface ErrMsg extends IndexedDBError {
	message: string;
}
interface MapObject extends Object {
	map: string;
	ctr: string;
	zoom: string;
	track: string;
	time: Date;
	poly: string;
}
interface EventError extends EventTarget {
	error: string;
}
/**
 * @fileoverview This script handles the indexedDB access for offline maps.
 * @author Ken Cowles
 * @version 1.0 First release
 * 
 * Each user will have his/her own browser-specific 'map_data' indexedDB;
 * it will include the map name, the map center points [lat, lng], whether
 * or not a track was imported, zoom level, and the track polyline if it exists.
 * 
 * Object = {map: "name", ctr: "lat,lng", zoom: "level", track: "y"/"n",
 *           time: "timestamp", poly: "polydata"};
 * 
 * This information is required in order to establish the mobile device offline
 * map. The timestamp is added for notification of age - older data will be 
 * automatically deleted (not implemented yet). Each object must have the "map"
 * property as specified by the objectStore, and this is the unique key by which
 * to access data. There are no db structural changes anticipated at this time,
 * hence no version tracking or updating is required. All objects will be added
 * to the same store. The following are the IndexedDB functions for use in
 * storing/reading/deleting map data.
 */
const DBNAME = 'maps';
const STORE  = 'map_data';
function openDB() {
	return new Promise((resolve, reject) => {
		const openRequest = window.indexedDB.open(DBNAME, 1);
		openRequest.onsuccess = (ev) => {
			var targ = ev.target as IndexedReqResult;
			var db = targ.result;
			//console.log("db opened...");
			resolve(db);
		}
		openRequest.onerror = (e) => {
			var targ = e.target as IndexedReqResult;
			var msg = "Cannot open 'maps' database " + targ.errorCode
			reject(msg);
            //alert(msg);
		};
		//openRequest.onblocked = () => console.warn('pending till unblocked');
		
		// one-time creation per user & browser:
		openRequest.onupgradeneeded = (e: IDBVersionChangeEvent) => {
			var targ = e.target as IndexedReqResult
			const db = targ.result as IDBDatabase; // local scope
			const objectStore = db.createObjectStore(STORE, { keyPath: "map" });
			objectStore.createIndex("time", "time", { unique: false });
			objectStore.createIndex("track", "track", {unique: false});
		};
	});
}

async function storeMap(mapname: string, center: number[], zoom: number,
	trk: boolean, poly = "no track") {
const mapctr = center.toString();
const zlevel = zoom.toString();
const imported = trk ? "y" : "n";
const stamp = new Date();
const mapdat: MapObject = {map: mapname, ctr: mapctr, zoom: zlevel,
	track: imported, time: stamp, poly: poly};
var openedDB = await openDB() as IDBDatabase;
await addMap(openedDB, mapdat);
openedDB.close();
return;
}

function addMap(db: IDBDatabase, mapObject: MapObject) {
	return new Promise( (resolve, reject) => {
		const tx = db.transaction(STORE, 'readwrite');
		const store = tx.objectStore(STORE);
		const request = store.add(mapObject);
		tx.oncomplete = () => {
            //console.log("Transaction complete");
        }
		request.onsuccess = () => {
			console.log("Map added");
			resolve(request.result);
		}
		request.onerror = () => {
			//alert("Could not save map: " + request.error);
			reject(request.error);
		}
	});
}

async function readMapData(mapname: string) {
	var open_db = await openDB() as IDBDatabase;
	var map_obj = await getMap(open_db, mapname) as MapObject;
	let ctr   = map_obj.ctr;
	let zm    = map_obj.zoom;
	let itrk  = map_obj.track;
	let time  = map_obj.time;
	var trackPoly = "" as unknown;
	trackPoly as L.Polyline<GeoJSON.LineString | GeoJSON.MultiLineString, any>;
	if (itrk === "y") {
		let polyline = map_obj.poly as string;
		let polydata = polyline.split(",");
		let latlngs:number[][] = [];
		var latlng:number[] = [0.0, 0.0];
		polydata.forEach((gps, indx) => {
			if (indx%2 === 0) {
				latlng[0] = parseFloat(gps);
			} else {
				latlng[1] = parseFloat(gps);
				latlngs.push(latlng);
				latlng = [0.0, 0.0];
			}
		});
		var poly = latlngs as any;
		trackPoly = L.polyline(poly) 
    }
	open_db.close();
	return [ctr, zm, itrk, time, trackPoly];
}
function getMap(db: IDBDatabase, map: string) {
	return new Promise( (resolve, reject) => {
		const tx = db.transaction(STORE, 'readonly');
		const store = tx.objectStore(STORE);
		const request = store.get(map)
		request.onerror = () => {
			let msg = `Failed to retrieve track for ${map}`;
			alert(msg);
			reject(msg)
		}
		request.onsuccess = () => {
			resolve(request.result);
		}
	});
}

async function removeMap(map: string) {
	const open_db = await openDB() as IDBDatabase;
	const tx = open_db.transaction(STORE, "readwrite");
	const  objStore = tx.objectStore(STORE);
	const request = objStore.delete(map);
	request.onsuccess = () => {
		console.log(`Deleted ${map}`);
	}
	request.onerror = ()  => {
		let msg = `Failed to delete ${map}`;
		alert(msg);
	}
}

async function readMapKeys() {
	var open_db = await openDB() as IDBDatabase;
	var k_data  = await getKeyData(open_db);
	return k_data;
}
function getKeyData(db: IDBDatabase) {
	return new Promise( (resolve, reject) => {
		const tx = db.transaction(STORE, "readonly");
		const  objectStore = tx.objectStore(STORE);
		const mapIndex = objectStore.index("track");
		const getData = mapIndex.getAllKeys();
		getData.onsuccess = () => {
			resolve(getData.result);
			// result is a string, but typescript type is complex
		}
		getData.onerror = () =>{
			reject( "Failed to read map keys");
		}
	});
} 
async function clearObjectStore() {
    var opened_db = await openDB() as IDBDatabase;
    await clearStore(opened_db, STORE);
    opened_db.close();
    return;
}
function clearStore(db: IDBDatabase, store: string) {
    return new Promise((resolve, reject) => {
        const transaction = db.transaction([store], 'readwrite');
        const objectStore = transaction.objectStore(store);
        const request = objectStore.clear();
    
        request.onsuccess = () => {
            console.log("Object store " + store + " cleared successfully.");
            resolve("cleared");
        };
        request.onerror = (event) => {
			var etarget = event.target as EventError;
            console.error("Error clearing object store " + store + 
                "; " + etarget.error);
            reject(etarget.error);
        };
        transaction.onerror = (event) => {
			var etarget = event.target as EventError;
            console.error(`Transaction error for clearing: `, 
                etarget.error);
        };
    });
}


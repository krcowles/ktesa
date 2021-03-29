/// <reference types="jquery" />
interface ChartMargins {
    top: number;
    left: number;
    right: number;
    bottom: number;
}
interface ChartRenderTypes {
    lines: string;
    points: string;
}
declare type ChartReturns = {
    renderType: ChartRenderTypes;
    render(canvasId: string, dataObj: ChartData): void;
};
interface Coords {
    x: number;
    y: number;
    px?: number;
    py?: number;
}
interface ChartData {
    renderTypes: ChartRenderTypes;
    dataPoints: Coords[];
    title: string;
    minY: number;
    maxY: number;
    labelFont: string;
    xLabel: string;
    yLabel: string;
    dataPointFont: string;
}
interface XAxisIncrement {
    XaxisPx: number;
    XaxisVal: number;
    MaxXIncs: number;
    LastXVal: number;
}
interface MapWindow extends Window {
    mrkrSet: boolean;
    chartMrkr: any;
    drawMarker: Function;
    mapdone: JQueryDeferred<void>;
}
interface TicValues {
    XaxisPx: number;
    XaxisVal: number;
    MaxXIncs: number;
    LastXVal: number;
}
//declare var chartWidth: number;
/**
 * Immediately Executed Function defining margin and render type, and returning
 * an object with renderType specified along with the main 'render' function.
 */
//declare const ChartObj: () => ChartReturns;
/**
 * This is the main driver for calling the various rendering functions
 */
//declare const renderChart: (title: string) => void;
//declare var getMaxDataYValue: () => void;
/**
 * This function determines how much horizontal distance should be placed
 * between X-axis tick marks.
 */
//declare var getXInc: () => TicValues;
/**
 * Fill the elevation chart's background with white
 */
//declare const renderBackground: () => void;
/**
 * This function statement sets the labels for the x and y axis, based on
 * the global object 'data' properties 'labelFont', 'xLabel', and 'yLabel'
 * It then situates the labels in the canvas context
 */
//declare const renderText: (title: string) => void;
/**
 * This function creates the chart 'grid' lines, tick marks and
 * corresponding tick mart text.
 */
//declare const renderLinesAndLabels: () => void;
//declare const drawLine: (startX: number, startY: number, endX: number, endY: number, strokeStyle: string, lineWidth: number) => void;
/**
 * This function will create the 'info box' as the user mouses over the chart. The
 * box is drawn on the canvas and displays the x & y coordinate values in miles/feet.
 * It is invoked in dynamicChart.js
 */
//declare const infoBox: (xloc: number, yloc: number, xval: string, yval: string, mapLink: GPSCoords) => void;
/**
 * This is the function that places lines between coordinates on the canvas.
 */
//declare var renderData: (type: string) => void;
/**
 * This simple function takes an integer, and if > 999 inserts commas as needed
 */
//declare function Thousands(value: number): string;

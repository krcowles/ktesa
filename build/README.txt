There are three types of files and associated scripts contained in this directory,
depending on whether one wishes to create a page, edit a page, or rebuild a page.

1. PAGE CREATION:
	invoke:   enterHike.html  ----> if Hike:      	validateHike.php
												  	displayHikePg.php
												  	saveHike.php								
							  ----> if Index:		makeIndexPg.php
													saveIndex.php
														
														
2. PAGE EDITING:			  ----> if Hike:
									invoke			hikeEditor.php
													editDB.php
													saveChanges.php
							  ----> if Index:
							  		invoke			indexEditor.php
							  						editIndx.php
							  						saveIndexChgs.php
							  						
3. REBUILD HIKE PAGE (should no longer be required)
		invoke:	selectRebuild.php
				rebuildData.php
				saveChanges.php
				
				
				
							  						
	
												
	
	
	+--------------------------------------------------------------------------------------------+
	|                                                                                            |
	|                 HAVING PROBLEMS? NEED HELP? DOESN'T WORK? WANT TO SAY HELLO?               |
	|                                                                                            |
	|		                       WRITE ME, I'M GLAD TO HELP                                    |
	|                                                                                            |
	|                                SVEN@FRANCODACOSTA.COM                                      |
	|                                                                                            |
	+--------------------------------------------------------------------------------------------+
	
	
	+--------------------------------------------------------------------------------------------+
	|	PROJECT LINKS                                                                            |
	+--------------------------------------------------------------------------------------------+
	|                                                                                            |
	|    USAGE EXAMPLES:                                                                         |
	|          http://www.francodacosta.com/phmagick/usage-examples                              |
	|                                                                                            |
	|    PROJECT HOME:                                                                           |
	|          http://www.francodacosta.com/phmagick/                                            |
	|                                                                                            |
	|    ANNOUNCEMENTS FEED:                                                                     |
	|          http://www.francodacosta.com/category/announcements/feed                          |
	|                                                                                            |
	+--------------------------------------------------------------------------------------------+
	
	
	+--------------------------------------------------------------------------------------------+
	|	DISCLAIMER - LEGAL NOTICE - LICENCING (GPL V3)                                           |
	+--------------------------------------------------------------------------------------------+
	|                                                                                            |
	|  This program is free software; you can redistribute it and/or modify it under the terms   |
	|  of the GNU General Public License version 3 as published by the Free Software Foundation  |
	|                                                                                            |
	|  This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; |
	|  without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. |
	|  See the GNU General Public License for more details.                                      |
	|                                                                                            |
	|  You should have received a copy of the GNU General Public License along with this         |
	|  program, if not you can obtain it at http://www.gnu.org/licenses/gpl-3.0.html             |
	|                                                                                            |
	+--------------------------------------------------------------------------------------------+
	
	
	+--------------------------------------------------------------------------------------------+
	|	CHANGE LOG                                                                               |
	+--------------------------------------------------------------------------------------------+
	|                                                                                            |
	|   20081210 - added support for non transparent images like jpg to polaroid and             | 
	|              fakepolaroid (you can set the image background color)                         |
	|            - Added border color and shade color to polaroid                                |
	|            - set class vars to protrcted so phMagick can be sub-classed                    |
	|                                                                                            |
	|   20081124 - added ability to change system wide default text formats                      |
	|            - rotate() can now handle transparent backgrounds                               |
	|                                                                                            |
	|	20081122 - added -strip to resize (smaller file size)                                    |
	|			 - added default value for resize() $height                                      |
	|			 - added dropShadow()                                                            |
	|			 - added roundCorner()                                                           |
	|			 - added fakePolaroid()                                                          |
	|			 - added polaroid()                                                              |
	|	                                                                                         |
	|	20081121 - due to users requests invert() was renamed to inverColors(),                  |
	|              it makes more sence                                                           |
	|	                                                                                         |
	|	20081020 - added function acquire() to get x frames/pages from video or pdf              |
	|	                                                                                         |
	|			 - updated class url to the correct one                                          |
	|			                                                                                 |
	| 			 - bug:: added return this to setSource()                                        |
	| 			                                                                                 |
	| 			 - bug:: added return this to setWebserverPath()                                 |
	| 			                                                                                 |
	|	20081010 - ontheFly() :: removed dependency of CONFIG class                              |
	|			   (http://www.francodacosta.com/php/you-are-here-how-hard-can-it-be)            |
	|			   by adding setPhysicalPath() and setWebserverPath()                            |
	|			                                                                                 |
	|			 - bug:: fixed setImageQuality() not returning $this                             |
	|			                                                                                 |
	|			 - bug:: removed getHistory() extra return|                                      |
	|	                                                                                         |
	+--------------------------------------------------------------------------------------------+
/*
    geoxml3.js

    Renders KML on the Google Maps JavaScript API Version 3 
    http://code.google.com/p/geoxml3/

   Copyright 2010 Sterling Udell, Larry Ross

   Licensed under the Apache License, Version 2.0 (the "License");
   you may not use this file except in compliance with the License.
   You may obtain a copy of the License at

       http://www.apache.org/licenses/LICENSE-2.0

   Unless required by applicable law or agreed to in writing, software
   distributed under the License is distributed on an "AS IS" BASIS,
   WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
   See the License for the specific language governing permissions and
   limitations under the License.

*/
/**
 * A MultiGeometry object that will allow multiple polylines in a MultiGeometry
 * containing LineStrings to be treated as a single object
 *
 * @param {MutiGeometryOptions} anonymous object.  Available properties:
 * map: The map on which to attach the MultiGeometry
 * paths: the individual polylines
 * polylineOptions: options to use when constructing all the polylines
 *
 * @constructor
 */
// only if Google Maps API included
if (!!window.google && !! google.maps) { 
function MultiGeometry(multiGeometryOptions) {
   function createPolyline(polylineOptions, mg) {
     var polyline = new google.maps.Polyline(polylineOptions);
     google.maps.event.addListener(polyline,'click', function(evt) { google.maps.event.trigger(mg,'click',evt);});
     google.maps.event.addListener(polyline,'dblclick', function(evt) { google.maps.event.trigger(mg, 'dblclick', evt);});
     google.maps.event.addListener(polyline,'mousedown', function(evt) { google.maps.event.trigger(mg, 'mousedown', evt);});
     google.maps.event.addListener(polyline,'mousemove', function(evt) { google.maps.event.trigger(mg, 'mousemove', evt);});
     google.maps.event.addListener(polyline,'mouseout', function(evt) { google.maps.event.trigger(mg, 'mouseout', evt);});
     google.maps.event.addListener(polyline,'mouseover', function(evt) { google.maps.event.trigger(mg, 'mouseover', evt);});
     google.maps.event.addListener(polyline,'mouseup', function(evt) { google.maps.event.trigger(mg, 'mouseup', evt);});
     google.maps.event.addListener(polyline,'rightclick', function(evt) { google.maps.event.trigger(mg, 'rightclick', evt);});
     return polyline;
   }
   this.setValues(multiGeometryOptions);
   this.polylines = [];

   for (i=0; i<this.paths.length;i++) {
     var polylineOptions = multiGeometryOptions;
     polylineOptions.path = this.paths[i];
     var polyline = createPolyline(polylineOptions,this);
     // Bind the polyline properties to the MultiGeometry properties
     this.polylines.push(polyline);
   }
}
MultiGeometry.prototype = new google.maps.MVCObject();
MultiGeometry.prototype.changed = function(key) {
    // alert(key+" changed");
    if (this.polylines) {
    for (var i=0; i<this.polylines.length; i++) {
        this.polylines[i].set(key,this.get(key));
    }
    }
};
MultiGeometry.prototype.setMap = function(map) { this.set('map',map); };
MultiGeometry.prototype.getMap = function() { return this.get('map'); };
}

// Extend the global String object with a method to remove leading and trailing whitespace
if (!String.prototype.trim) {
  String.prototype.trim = function () {
    return this.replace(/^\s+|\s+$/g, '');
  };
}

// Declare namespace
geoXML3 = window.geoXML3 || {instances: []};

// Constructor for the root KML parser object
geoXML3.parser = function (options) {
  // Inherit from Google MVC Object to include event handling   
  google.maps.MVCObject.call(this);   

  // Private variables
  var parserOptions = geoXML3.combineOptions(options, {
    singleInfoWindow: false,
    processStyles: true,
    zoom: true
  });
  var docs = []; // Individual KML documents
  var lastPlacemark;
  var parserName;
  if (typeof parserOptions.suppressInfoWindows == "undefined") parserOptions.suppressInfoWindows = false;
  if (!parserOptions.infoWindow && parserOptions.singleInfoWindow && !!window.google && !!google.maps)
    parserOptions.infoWindow = new google.maps.InfoWindow();

  geoXML3.xhrTimeout = 60000;
  if (!!parserOptions.xhrTimeout) geoXML3.xhrTimeout = parserOptions.xhrTimeout; 

  var parseKmlString = function (kmlString, docSet) {
    // Internal values for the set of documents as a whole
    var internals = {
      parser: this,
      docSet: docSet || [],
      remaining: 1,
      parseOnly: !(parserOptions.afterParse || parserOptions.processStyles)
    };
    thisDoc = new Object();
    thisDoc.internals = internals;
    internals.docSet.push(thisDoc);
    render(geoXML3.xmlParse(kmlString),thisDoc);
  }

  var parse = function (urls, docSet) {
    // Process one or more KML documents
    if (!parserName) {
      parserName = 'geoXML3.instances[' + (geoXML3.instances.push(this) - 1) + ']';
    }
    
    if (typeof urls === 'string') {
      // Single KML document
      urls = [urls];
    }

    // Internal values for the set of documents as a whole
    var internals = {
      parser: this,
      docSet: docSet || [],
      remaining: urls.length,
      parseOnly: !(parserOptions.afterParse || parserOptions.processStyles)
    };
    var thisDoc, j;
    for (var i = 0; i < urls.length; i++) {
      var baseUrl = urls[i].split('?')[0];
      for (j = 0; j < docs.length; j++) {
        if (baseUrl === docs[j].baseUrl) {
          // Reloading an existing document
          thisDoc = docs[j];
          thisDoc.reload    = true;
          break;
        }
      }
      if (j >= docs.length) {
        thisDoc = new Object();
        thisDoc.baseUrl = baseUrl;
        internals.docSet.push(thisDoc);
      }
      thisDoc.url       = urls[i];
      thisDoc.internals = internals;
      var url = thisDoc.url;
      if (parserOptions.proxy) url = parserOptions.proxy+thisDoc.url; 
      fetchDoc(url, thisDoc);
    }
  };

  function fetchDoc(url, doc) {
      geoXML3.fetchXML(url, function (responseXML) { 
        render(responseXML, doc);
      });
  }

  function __fetchDoc(url, doc, resFunc) {
    resFunc = resFunc || function (responseXML) {
      render(responseXML, doc); 
    };

    if (typeof ZipFile === 'function' && typeof JSIO === 'object' && typeof JSIO.guessFileType === 'function') {  // KMZ support requires these modules loaded
      // if url is a data URI scheme, do not guess type based on extension.
      if (/^data:[^,]*(kmz)/.test(doc.baseUrl)) {
         contentType = JSIO.FileType.Binary;
      } else if (/^data:[^,]*(kml|xml)/.test(doc.baseUrl)) {
         contentType = JSIO.FileType.XML;
      } else if (/^data:/.test(doc.baseUrl)) {
         contentType = JSIO.FileType.Unknown;
      } else if (parserOptions.forceZip) {  
         contentType = JSIO.FileType.Binary;
      } else {
         contentType = JSIO.guessFileType(doc.baseUrl);
      }
      if (contentType == JSIO.FileType.Binary || contentType == JSIO.FileType.Unknown) {
         doc.isCompressed = true;
         doc.baseDir = doc.baseUrl + '/';
         geoXML3.fetchZIP(url, resFunc, doc.internals.parser);
         return;
      }
    }
    doc.isCompressed = false;
    doc.baseDir = defileURL(doc.baseUrl);
    geoXML3.fetchXML(url, resFunc);
  }

  var hideDocument = function (doc) {
    if (!doc) doc = docs[0];
    // Hide the map objects associated with a document 
    var i;
    if (!!window.google && !!google.maps) {
      if (!!doc.markers) {
        for (i = 0; i < doc.markers.length; i++) {
          if(!!doc.markers[i].infoWindow) doc.markers[i].infoWindow.close();
          doc.markers[i].setVisible(false);
        }
      }
      if (!!doc.ggroundoverlays) {
        for (i = 0; i < doc.ggroundoverlays.length; i++) {
          doc.ggroundoverlays[i].setOpacity(0);
        }
      }
      if (!!doc.gpolylines) {
        for (i=0;i<doc.gpolylines.length;i++) {
          if(!!doc.gpolylines[i].infoWindow) doc.gpolylines[i].infoWindow.close();
          doc.gpolylines[i].setMap(null);
        }
      }
      if (!!doc.gpolygons) {
        for (i=0;i<doc.gpolygons.length;i++) {
          if(!!doc.gpolygons[i].infoWindow) doc.gpolygons[i].infoWindow.close();
         doc.gpolygons[i].setMap(null);
        }
      }
    }
  };
  
  var showDocument = function (doc) {
    if (!doc) doc = docs[0];
    // Show the map objects associated with a document 
    var i;
    if (!!window.google && !!google.maps) {
      if (!!doc.markers) {
        for (i = 0; i < doc.markers.length; i++) {
          doc.markers[i].setVisible(true);
        }
      }
      if (!!doc.ggroundoverlays) {
        for (i = 0; i < doc.ggroundoverlays.length; i++) {
          doc.ggroundoverlays[i].setOpacity(doc.ggroundoverlays[i].percentOpacity_);
        }
      }
      if (!!doc.gpolylines) {
        for (i=0;i<doc.gpolylines.length;i++) {
          doc.gpolylines[i].setMap(parserOptions.map);
        }
      }
      if (!!doc.gpolygons) {
        for (i=0;i<doc.gpolygons.length;i++) {
          doc.gpolygons[i].setMap(parserOptions.map);
        }
      }
    }
  };

var defaultStyle = {
  color: "ff000000", // black
  colorMode: "normal",
  width: 1,
  fill: true,
  outline: true,
  fillcolor: "3fff0000" // blue
};

function processStyle(thisNode, styles, styleID) {
      var nodeValue  = geoXML3.nodeValue;
      styles[styleID] = styles[styleID] || clone(defaultStyle);
      var styleNodes = thisNode.getElementsByTagName('IconStyle');
      if (!!styleNodes && !!styleNodes.length && (styleNodes.length > 0)) {
        styles[styleID].scale = parseFloat(nodeValue(styleNodes[0].getElementsByTagName('scale')[0]));
      }
      if (isNaN(styles[styleID].scale)) styles[styleID].scale = 1.0;
      styleNodes = thisNode.getElementsByTagName('Icon');
      if (!!styleNodes && !!styleNodes.length && (styleNodes.length > 0)) {
        styles[styleID].href = nodeValue(styleNodes[0].getElementsByTagName('href')[0]);
      }
      styleNodes = thisNode.getElementsByTagName('LineStyle');
      if (!!styleNodes && !!styleNodes.length && (styleNodes.length > 0)) {
        styles[styleID].color = nodeValue(styleNodes[0].getElementsByTagName('color')[0],defaultStyle.color);
        styles[styleID].colorMode = nodeValue(styleNodes[0].getElementsByTagName('colorMode')[0], defaultStyle.colorMode);
        styles[styleID].width = nodeValue(styleNodes[0].getElementsByTagName('width')[0],defaultStyle.width);
      }
      styleNodes = thisNode.getElementsByTagName('PolyStyle');
      if (!!styleNodes && !!styleNodes.length && (styleNodes.length > 0)) {
        styles[styleID].outline   = getBooleanValue(styleNodes[0].getElementsByTagName('outline')[0],defaultStyle.outline);
        styles[styleID].fill      = getBooleanValue(styleNodes[0].getElementsByTagName('fill')[0],defaultStyle.fill);
        styles[styleID].colorMode = nodeValue(styleNodes[0].getElementsByTagName('colorMode')[0], defaultStyle.colorMode);
        styles[styleID].fillcolor = nodeValue(styleNodes[0].getElementsByTagName('color')[0],defaultStyle.fillcolor);
      }
      return styles[styleID];
}

// from http://stackoverflow.com/questions/122102/what-is-the-most-efficient-way-to-clone-a-javascript-object
// http://keithdevens.com/weblog/archive/2007/Jun/07/javascript.clone
  function clone(obj){
      if(obj == null || typeof(obj) != 'object') return obj;
      var temp = new obj.constructor(); 
      for(var key in obj) temp[key] = clone(obj[key]);
      return temp;
  }

function processStyleMap(thisNode, styles, styleID) {
  var nodeValue  = geoXML3.nodeValue;
  var pairs = thisNode.getElementsByTagName('Pair');
  var map = new Object();
  // add each key to the map
  for (var pr=0;pr<pairs.length;pr++) {
    var pairkey = nodeValue(pairs[pr].getElementsByTagName('key')[0]);
    var pairstyle = nodeValue(pairs[pr].getElementsByTagName('Style')[0]);
    var pairstyleurl = nodeValue(pairs[pr].getElementsByTagName('styleUrl')[0]);
    if (!!pairstyle) {
      processStyle(pairstyle, map[pairkey], styleID);
    } else if (!!pairstyleurl && !!styles[pairstyleurl]) {
      map[pairkey] = clone(styles[pairstyleurl]);
    }
  }
  if (!!map["normal"]) {
    styles[styleID] = clone(map["normal"]);
  } else {
    styles[styleID] =  clone(defaultStyle);
  }      
  if (!!map["highlight"] && !!parserOptions.processStyles) {
    processStyleID(map["highlight"]);
  }
  styles[styleID].map = clone(map);
}

function getBooleanValue(node) {
  var nodeContents = geoXML3.nodeValue(node);
  if (!nodeContents) return true;
  if (nodeContents) nodeContents = parseInt(nodeContents);
  if (isNaN(nodeContents)) return true;
  if (nodeContents == 0) return false;
  else return true;
}

function processPlacemarkCoords(node, tag) {
    var parent = [];
    if (tag !== undefined)
        parent = node.getElementsByTagName(tag);
    else {
        parent.push(node);
    }
var coordListA = [];
  for (var i=0; i<parent.length; i++) {
  var coordNodes = parent[i].getElementsByTagName('coordinates')
  if (!coordNodes) {
    if (coordListA.length > 0) {
      break;
    } else {
      return [{coordinates: []}];
    }
  }

  for (var j=0; j<coordNodes.length;j++) { 
    var coords = geoXML3.nodeValue(coordNodes[j]).trim();
    coords = coords.replace(/,\s+/g, ',');
    var path = coords.split(/\s+/g);
    var pathLength = path.length;
    var coordList = [];
    for (var k = 0; k < pathLength; k++) {
      coords = path[k].split(',');
      if (!isNaN(coords[0]) && !isNaN(coords[1])) {
        coordList.push({
          lat: parseFloat(coords[1]), 
          lng: parseFloat(coords[0]), 
          alt: parseFloat(coords[2])
        });
      }
    }
    coordListA.push({coordinates: coordList});
  }
}
  return coordListA;
}

  var render = function (responseXML, doc) {
    if (parserOptions.afterFetch) {
      parserOptions.afterFetch(responseXML);
    }
    // Callback for retrieving a KML document: parse the KML and display it on the map
    if (!responseXML || responseXML == "failed parse") {
      // Error retrieving the data
      geoXML3.log('Unable to retrieve ' + doc.url);
      if (parserOptions.failedParse) {
        parserOptions.failedParse(doc);
      }
    } else if (!doc) {
      throw 'geoXML3 internal error: render called with null document';
    } else { //no errors

      var i;
      var styles = {};
      doc.placemarks     = [];
      doc.groundoverlays = [];
      doc.ggroundoverlays = [];
      doc.networkLinks   = [];
      doc.gpolygons      = [];
      doc.gpolylines     = [];
      doc.markers        = [];

    // Declare some helper functions in local scope for better performance
    var nodeValue  = geoXML3.nodeValue;

    // Parse styles
    var styleID, styleNodes;
    nodes = responseXML.getElementsByTagName('Style');
    nodeCount = nodes.length;
    for (i = 0; i < nodeCount; i++) {
      thisNode = nodes[i];
      var thisNodeId = thisNode.getAttribute('id');
      if (!!thisNodeId) {
        styleID    = '#' + thisNodeId;
        processStyle(thisNode, styles, styleID);
      }
    }
    // rudamentary support for StyleMap
    // use "normal" mapping only
    nodes = responseXML.getElementsByTagName('StyleMap');
    for (i = 0; i < nodes.length; i++) {
      thisNode = nodes[i];
      var thisNodeId = thisNode.getAttribute('id');
      if (!!thisNodeId) {
        styleID    = '#' + thisNodeId;
    processStyleMap(thisNode, styles, styleID);
      }
    }
    doc.styles = styles;
      if (!!parserOptions.processStyles || !parserOptions.createMarker) {
        // Convert parsed styles into GMaps equivalents
        processStyles(doc);
      }

      // Parse placemarks
      if (!!doc.reload && !!doc.markers) {
        for (i = 0; i < doc.markers.length; i++) {
          doc.markers[i].active = false;
        }
      }
      var placemark, node, coords, path, marker, poly;
      var placemark, coords, path, pathLength, marker, polygonNodes, coordList;
      var placemarkNodes = responseXML.getElementsByTagName('Placemark');
      for (pm = 0; pm < placemarkNodes.length; pm++) {
          // Init the placemark object
          node = placemarkNodes[pm];
          placemark = {
              name:  geoXML3.nodeValue(node.getElementsByTagName('name')[0]),
              description: geoXML3.nodeValue(node.getElementsByTagName('description')[0]),
              properties: geoXML3.nodeValue(node.getElementsByTagName('Properties')[0]),
              styleUrl: geoXML3.nodeValue(node.getElementsByTagName('styleUrl')[0]),
              id: node.getAttribute('id')
          };
          placemark.style = doc.styles[placemark.styleUrl] || clone(defaultStyle);
          // inline style overrides shared style
          var inlineStyles = node.getElementsByTagName('Style');
          if (inlineStyles && (inlineStyles.length > 0)) {
              var style = processStyle(node,doc.styles,"inline");
              processStyleID(style);
              if (style) placemark.style = style;
          }
          if (/^https?:\/\//.test(placemark.description)) {
              placemark.description = ['<a href="', placemark.description, '">', placemark.description, '</a>'].join('');
          }

          var multiNodes = node.getElementsByTagName('MultiGeometry'); // MultiGeometry if this tag exists
          if (!multiNodes || multiNodes.length == 0)
          {
              multiNodes = [];
              multiNodes.push(node); // Single Node - process placemark as an array element and it will pickup the shape as if a real multi shape.
          }
          if (!!multiNodes && multiNodes.length > 0) {
              // This is our (IMS's) rewrite of the open source version.  See comment below this logic ('// old geoXML3 logic').
              for (var mn = 0; mn < multiNodes.length; mn++) {
                  var pointNodes = multiNodes[mn].getElementsByTagName('Point');
                  var polygonNodes = multiNodes[mn].getElementsByTagName('Polygon');
                  var lineStringNodes = multiNodes[mn].getElementsByTagName('LineString');
                  for (var pn = 0; pn < pointNodes.length; pn++) {
                      placemark.Point = processPlacemarkCoords(pointNodes[pn])[0];
                      if (!!window.google && !!google.maps)
                          placemark.latlng = new google.maps.LatLng(placemark.Point.coordinates[0].lat, placemark.Point.coordinates[0].lng);
                      pathLength = 1;
                  }
                  for (var pg = 0; pg < polygonNodes.length; pg++) {
                      if (!placemark.Polygon)
                          placemark.Polygon = [{
                              outerBoundaryIs: { coordinates: [] },
                              innerBoundaryIs: [{ coordinates: [] }]
                          }];

                      placemark.Polygon[pg] = {
                          outerBoundaryIs: { coordinates: [] },
                          innerBoundaryIs: [{ coordinates: [] }]
                      }
                      placemark.Polygon[pg].outerBoundaryIs = processPlacemarkCoords(polygonNodes[pg], "outerBoundaryIs");
                      placemark.Polygon[pg].innerBoundaryIs = processPlacemarkCoords(polygonNodes[pg], "innerBoundaryIs");

                      coordList = placemark.Polygon[0].outerBoundaryIs;
                  }
                  for (var ls = 0; ls < lineStringNodes.length; ls++) {
                      pathLength = 0;
                      placemark.LineString = processPlacemarkCoords(lineStringNodes[ls]);
                  }
              }
          }
/*              // old geoXML3 logic
              // In the open source version, the code below was used for single and MultiGeometry shapes.  Even though  
              // it worked, the performance is poor because the looping is incorrect and it processes each polygon
              // multiple times.  If we use geoXML3, we'll look to submit the issue so the open source could be fixed.
              var GeometryNodes = node.getElementsByTagName('coordinates');
              var Geometry = null;
              if (!!GeometryNodes && (GeometryNodes.length > 0)) {
                  for (var gn = 0; gn < GeometryNodes.length; gn++) {
                      if (!GeometryNodes[gn].parentNode ||
                          !GeometryNodes[gn].parentNode.nodeName) {

                      } else { // parentNode.nodeName exists
                          var GeometryPN = GeometryNodes[gn].parentNode;
                          Geometry = GeometryPN.nodeName;

                          // Extract the coordinates
                          // What sort of placemark?
                          switch (Geometry) {
                              case "Point":
                                  placemark.Point = processPlacemarkCoords(node, "Point")[0];
                                  if (!!window.google && !!google.maps)
                                      placemark.latlng = new google.maps.LatLng(placemark.Point.coordinates[0].lat, placemark.Point.coordinates[0].lng);
                                  pathLength = 1;
                                  break;
                              case "LinearRing":
                                  // Polygon/line
                                  polygonNodes = node.getElementsByTagName('Polygon');
                                  // Polygon
                                  if (!placemark.Polygon)
                                      placemark.Polygon = [{
                                          outerBoundaryIs: { coordinates: [] },
                                          innerBoundaryIs: [{ coordinates: [] }]
                                      }];
                                  for (var pg = 0; pg < polygonNodes.length; pg++) {
                                      placemark.Polygon[pg] = {
                                          outerBoundaryIs: { coordinates: [] },
                                          innerBoundaryIs: [{ coordinates: [] }]
                                      }
                                      placemark.Polygon[pg].outerBoundaryIs = processPlacemarkCoords(polygonNodes[pg], "outerBoundaryIs");
                                      placemark.Polygon[pg].innerBoundaryIs = processPlacemarkCoords(polygonNodes[pg], "innerBoundaryIs");
                                  }
                                  coordList = placemark.Polygon[0].outerBoundaryIs;
                                  break;

                              case "LineString":
                                  pathLength = 0;
                                  placemark.LineString = processPlacemarkCoords(node, "LineString");
                                  break;

                              default:
                                  break;
                          }
                      } // parentNode.nodeName exists
                  } // GeometryNodes loop
              } // if GeometryNodes*/

      // call the custom placemark parse function if it is defined
      if (!!parserOptions.pmParseFn) parserOptions.pmParseFn(node, placemark);
      doc.placemarks.push(placemark);
      if (!!window.google && !!google.maps) {
       if (placemark.Point) {
          if (!!window.google && !!google.maps) {
            doc.bounds = doc.bounds || new google.maps.LatLngBounds();
            doc.bounds.extend(placemark.latlng);
          }

          if (!!parserOptions.createMarker) {
            // User-defined marker handler
            parserOptions.createMarker(placemark, doc);
          } else { // !user defined createMarker
            // Check to see if this marker was created on a previous load of this document
            var found = false;
            if (!!doc) {
              doc.markers = doc.markers || [];
              if (doc.reload) {
                for (var j = 0; j < doc.markers.length; j++) {
                    if ((doc.markers[j].id == placemark.id) ||
            // if no id, check position
                        (!doc.markers[j].id && 
                         (doc.markers[j].getPosition().equals(placemark.latlng)))) {
                    found = doc.markers[j].active = true;
                    break;
                  }
                }
              } 
            }

            if (!found) {
              // Call the built-in marker creator
              marker = createMarker(placemark, doc);
              if (marker) { 
                marker.active = true;
                marker.id = placemark.id;
              }
            }
          }
         }
         if (placemark.Polygon) { // poly test 2
          if (!!doc) {
           doc.gpolygons = doc.gpolygons || [];
          }

          if (!!parserOptions.createPolygon) {
           // User-defined polygon handler
            poly = parserOptions.createPolygon(placemark, doc);
          } else {  // ! user defined createPolygon
           // Check to see if this marker was created on a previous load of this document
           poly = createPolygon(placemark,doc);
           poly.active = true;
          }
          if (!!window.google && !!google.maps) {
           doc.bounds = doc.bounds || new google.maps.LatLngBounds();
           doc.bounds.union(poly.bounds);
          }
         } 
         if (placemark.LineString) { // polyline
          if (!!doc) {
           doc.gpolylines = doc.gpolylines || [];
          }
          if (!!parserOptions.createPolyline) {
           // User-defined polyline handler
           poly = parserOptions.createPolyline(placemark, doc);
          } else { // ! user defined createPolyline
           // Check to see if this marker was created on a previous load of this document
           poly = createPolyline(placemark,doc);
           poly.active = true;
          }
          if (!!window.google && !!google.maps) {
           doc.bounds = doc.bounds || new google.maps.LatLngBounds();
           doc.bounds.union(poly.bounds);
          }
         }
       }
      } // placemark loop

      if (!!doc.reload && !!doc.markers) {
        for (i = doc.markers.length - 1; i >= 0 ; i--) {
          if (!doc.markers[i].active) {
            if (!!doc.markers[i].infoWindow) {
              doc.markers[i].infoWindow.close();
            }
            doc.markers[i].setMap(null);
            doc.markers.splice(i, 1);
          }
        }
      }

      // Parse ground overlays
      if (!!doc.reload && !!doc.groundoverlays) {
        for (i = 0; i < doc.groundoverlays.length; i++) {
          doc.groundoverlays[i].active = false;
        }
      }

      if (!!doc) {
        doc.groundoverlays = doc.groundoverlays || [];
      }
      // doc.groundoverlays =[];
      var groundOverlay, color, transparency, overlay;
      var groundNodes = responseXML.getElementsByTagName('GroundOverlay');
      for (i = 0; i < groundNodes.length; i++) {
        node = groundNodes[i];
        
        // Init the ground overlay object
        groundOverlay = {
          name:        geoXML3.nodeValue(node.getElementsByTagName('name')[0]),
          description: geoXML3.nodeValue(node.getElementsByTagName('description')[0]),
          icon: {href: geoXML3.nodeValue(node.getElementsByTagName('href')[0])},
          latLonBox: {
            north: parseFloat(geoXML3.nodeValue(node.getElementsByTagName('north')[0])),
            east:  parseFloat(geoXML3.nodeValue(node.getElementsByTagName('east')[0])),
            south: parseFloat(geoXML3.nodeValue(node.getElementsByTagName('south')[0])),
            west:  parseFloat(geoXML3.nodeValue(node.getElementsByTagName('west')[0]))
          }
        };
        if (!!window.google && !!google.maps) {
          doc.bounds = doc.bounds || new google.maps.LatLngBounds();
          doc.bounds.union(new google.maps.LatLngBounds(
            new google.maps.LatLng(groundOverlay.latLonBox.south, groundOverlay.latLonBox.west),
            new google.maps.LatLng(groundOverlay.latLonBox.north, groundOverlay.latLonBox.east)
          ));
        }

        // Opacity is encoded in the color node
        var colorNode = node.getElementsByTagName('color');
        if ( colorNode && colorNode.length && (colorNode.length > 0)) {
         groundOverlay.opacity = geoXML3.getOpacity(nodeValue(colorNode[0]));
        } else {
         groundOverlay.opacity = 0.45;
        }

        doc.groundoverlays.push(groundOverlay);
        if (!!window.google && !!google.maps) {
         if (!!parserOptions.createOverlay) {
          // User-defined overlay handler
          parserOptions.createOverlay(groundOverlay, doc);
         } else { // ! user defined createOverlay
          // Check to see if this overlay was created on a previous load of this document
          var found = false;
          if (!!doc) {
            doc.groundoverlays = doc.groundoverlays || [];
            if (!!window.google && !!google.maps && doc.reload) {
                overlayBounds = new google.maps.LatLngBounds(
                  new google.maps.LatLng(groundOverlay.latLonBox.south, groundOverlay.latLonBox.west),
                  new google.maps.LatLng(groundOverlay.latLonBox.north, groundOverlay.latLonBox.east));
              var overlays = doc.groundoverlays;
              for (i = overlays.length; i--;) {
                if ((overlays[i].bounds().equals(overlayBounds)) &&
                    (overlays.url_ === groundOverlay.icon.href)) {
                  found = overlays[i].active = true;
                  break;
                }
              }
            } 
          }
  
          if (!found) {
            // Call the built-in overlay creator
            overlay = createOverlay(groundOverlay, doc);
            overlay.active = true;
          }
     }
         if (!!doc.reload && !!doc.groundoverlays && !!doc.groundoverlays.length) {
          var overlays = doc.groundoverlays;
          for (i = overlays.length; i--;) {
           if (!overlays[i].active) {
            overlays[i].remove();
            overlays.splice(i, 1);
           }
          }
          doc.groundoverlays = overlays;
     }
    }
      }
      // Parse network links
      var networkLink;
      var docPath = document.location.pathname.split('/');
      docPath = docPath.splice(0, docPath.length - 1).join('/');
      var linkNodes = responseXML.getElementsByTagName('NetworkLink');
      for (i = 0; i < linkNodes.length; i++) {
        node = linkNodes[i];
        
        // Init the network link object
        networkLink = {
          name: geoXML3.nodeValue(node.getElementsByTagName('name')[0]),
          link: {
            href:        geoXML3.nodeValue(node.getElementsByTagName('href')[0]),
            refreshMode:     geoXML3.nodeValue(node.getElementsByTagName('refreshMode')[0])
          }
        };
        
        // Establish the specific refresh mode 
        if (networkLink.link.refreshMode === '') {
          networkLink.link.refreshMode = 'onChange';
        }
        if (networkLink.link.refreshMode === 'onInterval') {
          networkLink.link.refreshInterval = parseFloat(geoXML3.nodeValue(node.getElementsByTagName('refreshInterval')[0]));
          if (isNaN(networkLink.link.refreshInterval)) {
            networkLink.link.refreshInterval = 0;
          }
        } else if (networkLink.link.refreshMode === 'onChange') {
          networkLink.link.viewRefreshMode = geoXML3.nodeValue(node.getElementsByTagName('viewRefreshMode')[0]);
          if (networkLink.link.viewRefreshMode === '') {
            networkLink.link.viewRefreshMode = 'never';
          }
          if (networkLink.link.viewRefreshMode === 'onStop') {
            networkLink.link.viewRefreshTime = geoXML3.nodeValue(node.getElementsByTagName('refreshMode')[0]);
            networkLink.link.viewFormat =      geoXML3.nodeValue(node.getElementsByTagName('refreshMode')[0]);
            if (networkLink.link.viewFormat === '') {
              networkLink.link.viewFormat = 'BBOX=[bboxWest],[bboxSouth],[bboxEast],[bboxNorth]';
            }
          }
        }

        if (!/^[\/|http]/.test(networkLink.link.href)) {
          // Fully-qualify the HREF
          networkLink.link.href = docPath + '/' + networkLink.link.href;
        }

        // Apply the link
        if ((networkLink.link.refreshMode === 'onInterval') && 
            (networkLink.link.refreshInterval > 0)) {
          // Reload at regular intervals
          setInterval(parserName + '.parse("' + networkLink.link.href + '")', 
                      1000 * networkLink.link.refreshInterval); 
        } else if (networkLink.link.refreshMode === 'onChange') {
          if (networkLink.link.viewRefreshMode === 'never') {
            // Load the link just once
            doc.internals.parser.parse(networkLink.link.href, doc.internals.docSet);
          } else if (networkLink.link.viewRefreshMode === 'onStop') {
            // Reload when the map view changes
            
          }
        }
      }
}

      if (!!doc.bounds && !!window.google && !!google.maps) {
        doc.internals.bounds = doc.internals.bounds || new google.maps.LatLngBounds();
        doc.internals.bounds.union(doc.bounds); 
      }
      if (!!doc.markers || !!doc.groundoverlays || !!doc.gpolylines || !!doc.gpolygons) {
        doc.internals.parseOnly = false;
      }

      doc.internals.remaining -= 1;
      if (doc.internals.remaining === 0) {
        // We're done processing this set of KML documents
        // Options that get invoked after parsing completes
      if (parserOptions.zoom && !!doc.internals.bounds &&
          !doc.internals.bounds.isEmpty() && !!parserOptions.map) {
          parserOptions.map.fitBounds(doc.internals.bounds); 
        }
        if (parserOptions.afterParse) {
          parserOptions.afterParse(doc.internals.docSet);
        }

        if (!doc.internals.parseOnly) {
          // geoXML3 is not being used only as a real-time parser, so keep the processed documents around
            for (var i=0;i<doc.internals.docSet.length;i++) {
              docs.push(doc.internals.docSet[i]);
            }
        }
        google.maps.event.trigger(doc.internals.parser, 'parsed');   
      }
  };

  var kmlColor = function (kmlIn, colorMode) {
    var kmlColor = {};
    kmlIn = kmlIn || 'ffffffff';  // white (KML 2.2 default)

    var aa = kmlIn.substr(0,2);
    var bb = kmlIn.substr(2,2);
    var gg = kmlIn.substr(4,2);
    var rr = kmlIn.substr(6,2);

    kmlColor.opacity = parseInt(aa, 16) / 256;
    kmlColor.color   = (colorMode === 'random') ? randomColor(rr, gg, bb) : '#' + rr + gg + bb;
    return kmlColor;
  };

  // Implemented per KML 2.2 <ColorStyle> specs
  var randomColor = function(rr, gg, bb) {
    var col = { rr: rr, gg: gg, bb: bb };
    for (var k in col) {
      var v = col[k];
      if (v == null) v = 'ff';

      // RGB values are limiters for random numbers (ie: 7f would be a random value between 0 and 7f)
      v = Math.round(Math.random() * parseInt(rr, 16)).toString(16);
      if (v.length === 1) v = '0' + v;
      col[k] = v;
    }

    return '#' + col.rr + col.gg + col.bb;
  };

  var processStyleID = function (style) {
    if (!!window.google && !!google.maps) {
      var zeroPoint = new google.maps.Point(0,0);
      if (!!style.href) {
        var markerRegEx = /\/(red|blue|green|yellow|lightblue|purple|pink|orange|pause|go|stop)(-dot)?\.png/;
        if (markerRegEx.test(style.href)) {
         //bottom middle
      var anchorPoint = new google.maps.Point(16*style.scale, 32*style.scale);
    } else {
      var anchorPoint = new google.maps.Point(16*style.scale, 16*style.scale);
    }
        // Init the style object with a standard KML icon
        style.icon =  new google.maps.MarkerImage(
          style.href,
          new google.maps.Size(32*style.scale, 32*style.scale),
          zeroPoint,
          // bottom middle 
          anchorPoint,
          new google.maps.Size(32*style.scale, 32*style.scale)

        );

        // Look for a predictable shadow
        var stdRegEx = /\/(red|blue|green|yellow|lightblue|purple|pink|orange)(-dot)?\.png/;
        var shadowSize = new google.maps.Size(59, 32);
    var shadowPoint = new google.maps.Point(16,32);
        if (stdRegEx.test(style.href)) {
          // A standard GMap-style marker icon
          style.shadow = new google.maps.MarkerImage(
              'http://maps.google.com/mapfiles/ms/micons/msmarker.shadow.png',
              shadowSize,
              zeroPoint,
              shadowPoint,
              shadowSize);
        } else if (style.href.indexOf('-pushpin.png') > -1) {
          // Pushpin marker icon
          style.shadow = new google.maps.MarkerImage(
            'http://maps.google.com/mapfiles/ms/micons/pushpin_shadow.png',
            shadowSize,
            zeroPoint,
            shadowPoint,
            shadowSize);
        } else {
          // Other MyMaps KML standard icon
          style.shadow = new google.maps.MarkerImage(
            style.href.replace('.png', '.shadow.png'),                 
            shadowSize,                        
            zeroPoint,                  
            shadowPoint,
            shadowSize);
        }
      }
    }
  }
    
  var processStyles = function (doc) {
    for (var styleID in doc.styles) {
      processStyleID(doc.styles[styleID]);
    }
  };

  var createMarker = function (placemark, doc) {
    // create a Marker to the map from a placemark KML object

    // Load basic marker properties
    var markerOptions = geoXML3.combineOptions(parserOptions.markerOptions, {
      map:      parserOptions.map,
      position: new google.maps.LatLng(placemark.Point.coordinates[0].lat, placemark.Point.coordinates[0].lng),
      title:    placemark.name,
      zIndex:   Math.round(placemark.Point.coordinates[0].lat * -100000)<<5,
      icon:     placemark.style.icon,
      shadow:   placemark.style.shadow 
    });
  
    // Create the marker on the map
    var marker = new google.maps.Marker(markerOptions);
    if (!!doc) {
      doc.markers.push(marker);
    }

    if (parserOptions.afterCreateMarker) {
      parserOptions.afterCreateMarker(placemark, marker);
    }

    // Set up and create the infowindow if it is not suppressed
    if (!parserOptions.suppressInfoWindows) {
      var infoWindowOptions = geoXML3.combineOptions(parserOptions.infoWindowOptions, {
        content: '<div class="geoxml3_infowindow"><h3>' + placemark.name + 
                 '</h3><div>' + placemark.description + '</div></div>',
        pixelOffset: new google.maps.Size(0, 2)
      });
      if (parserOptions.infoWindow) {
        marker.infoWindow = parserOptions.infoWindow;
      } else {
        marker.infoWindow = new google.maps.InfoWindow(infoWindowOptions);
      }
      marker.infoWindowOptions = infoWindowOptions;
      
      // Infowindow-opening event handler
      google.maps.event.addListener(marker, 'click', function() {
        this.infoWindow.close();

        if (infoWindowOptions.markerOpen) {
          infoWindowOptions.markerOpen(placemark, marker.infoWindow, marker.infoWindowOptions);
        }

        marker.infoWindow.setOptions(this.infoWindowOptions);
        this.infoWindow.open(this.map, this);
      });
    }
    placemark.marker = marker;
    return marker;
  };
  
  var createOverlay = function (groundOverlay, doc) {
    // Add a ProjectedOverlay to the map from a groundOverlay KML object

    if (!window.ProjectedOverlay) {
      throw 'geoXML3 error: ProjectedOverlay not found while rendering GroundOverlay from KML';
    }

    var bounds = new google.maps.LatLngBounds(
        new google.maps.LatLng(groundOverlay.latLonBox.south, groundOverlay.latLonBox.west),
        new google.maps.LatLng(groundOverlay.latLonBox.north, groundOverlay.latLonBox.east)
    );
    var overlayOptions = geoXML3.combineOptions(parserOptions.overlayOptions, {percentOpacity: groundOverlay.opacity*100});
    var overlay = new ProjectedOverlay(parserOptions.map, groundOverlay.icon.href, bounds, overlayOptions);
    
    if (!!doc) {
      doc.ggroundoverlays = doc.ggroundoverlays || [];
      doc.ggroundoverlays.push(overlay);
    }

    return overlay;
  };

// Create Polyline
var createPolyline = function(placemark, doc) {
  var paths = [];
  var bounds = new google.maps.LatLngBounds();
  for (var j=0; j<placemark.LineString.length; j++) {
    var path = [];
    var coords = placemark.LineString[j].coordinates;
    for (var i=0;i<coords.length;i++) {
      var pt = new google.maps.LatLng(coords[i].lat, coords[i].lng);
      path.push(pt);
      bounds.extend(pt);
    }
    paths.push(path);
  }
    
  // point to open the infowindow if triggered 
  var point = paths[0][Math.floor(path.length/2)];
  // Load basic polyline properties
  var kmlStrokeColor = kmlColor(placemark.style.color,placemark.style.colorMode);
  var polyOptions = geoXML3.combineOptions(parserOptions.polylineOptions, {
    map:      parserOptions.map,
    strokeColor: kmlStrokeColor.color,
    strokeWeight: placemark.style.width,
    strokeOpacity: kmlStrokeColor.opacity,
    title:    placemark.name
  });
  if (paths.length > 1) {
    polyOptions.paths = paths;
    var p = new MultiGeometry(polyOptions);
  } else {
    polyOptions.path = paths[0];
    var p = new google.maps.Polyline(polyOptions);
  }
  p.bounds = bounds;
  // setup and create the infoWindow if it is not suppressed
  if (!parserOptions.suppressInfoWindows) {
    var infoWindowOptions = geoXML3.combineOptions(parserOptions.infoWindowOptions, {
      content: '<div class="geoxml3_infowindow"><h3>' + placemark.name + 
               '</h3><div>' + placemark.description + '</div></div>',
      pixelOffset: new google.maps.Size(0, 2)
    });
    if (parserOptions.infoWindow) {
      p.infoWindow = parserOptions.infoWindow;
    } else {
      p.infoWindow = new google.maps.InfoWindow(infoWindowOptions);
    }
    p.infoWindowOptions = infoWindowOptions;
    // Infowindow-opening event handler
    google.maps.event.addListener(p, 'click', function(e) {

      p.infoWindow.close();

      if (infoWindowOptions.polylineOpen) {
        infoWindowOptions.polylineOpen(placemark, p.infoWindow, p.infoWindowOptions);
      }

      p.infoWindow.setOptions(p.infoWindowOptions);
      if (e && e.latLng) {
        p.infoWindow.setPosition(e.latLng);
      } else {
        p.infoWindow.setPosition(point);
      }
      p.infoWindow.open(p.map || p.polylines[0].map);
    });
  }
  if (!!doc) doc.gpolylines.push(p);
  placemark.polyline = p;
  return p;
}

// Create Polygon
var createPolygon = function(placemark, doc) {
  var bounds = new google.maps.LatLngBounds();
  var pathsLength = 0;
  var paths = [];
  for (var polygonPart=0;polygonPart<placemark.Polygon.length;polygonPart++) {
    for (var j=0; j<placemark.Polygon[polygonPart].outerBoundaryIs.length; j++) {
      var coords = placemark.Polygon[polygonPart].outerBoundaryIs[j].coordinates;
      var path = [];
      for (var i=0;i<coords.length;i++) {
        var pt = new google.maps.LatLng(coords[i].lat, coords[i].lng);
        path.push(pt);
        bounds.extend(pt);
      }
      paths.push(path);
      pathsLength += path.length;
    }
    for (var j=0; j<placemark.Polygon[polygonPart].innerBoundaryIs.length; j++) {
      var coords = placemark.Polygon[polygonPart].innerBoundaryIs[j].coordinates;
      var path = [];
      for (var i=0;i<coords.length;i++) {
        var pt = new google.maps.LatLng(coords[i].lat, coords[i].lng);
        path.push(pt);
        bounds.extend(pt);
      }
      paths.push(path);
      pathsLength += path.length;
    }
  }

  // Load basic polygon properties
  var kmlStrokeColor = kmlColor(placemark.style.color,placemark.style.colorMode);
  var kmlFillColor = kmlColor(placemark.style.fillcolor,placemark.style.colorMode);
  if (!placemark.style.fill) kmlFillColor.opacity = 0.0;
  var strokeWeight = placemark.style.width;
  if (!placemark.style.outline) {
    strokeWeight = 0;
    kmlStrokeColor.opacity = 0.0;
  }
  var polyOptions = geoXML3.combineOptions(parserOptions.polygonOptions, {
    map:      parserOptions.map,
    paths:    paths,
    title:    placemark.name,
    strokeColor: kmlStrokeColor.color,
    strokeWeight: strokeWeight,
    strokeOpacity: kmlStrokeColor.opacity,
    fillColor: kmlFillColor.color,
    fillOpacity: kmlFillColor.opacity
  });
  var p = new google.maps.Polygon(polyOptions);
  p.bounds = bounds;
  if (!parserOptions.suppressInfoWindows) {
    var infoWindowOptions = geoXML3.combineOptions(parserOptions.infoWindowOptions, {
      content: '<div class="geoxml3_infowindow"><h3>' + placemark.name + 
               '</h3><div>' + placemark.description + '</div></div>',
      pixelOffset: new google.maps.Size(0, 2)
    });
    if (parserOptions.infoWindow) {
      p.infoWindow = parserOptions.infoWindow;
    } else {
      p.infoWindow = new google.maps.InfoWindow(infoWindowOptions);
    }
    p.infoWindowOptions = infoWindowOptions;
    // Infowindow-opening event handler
    google.maps.event.addListener(p, 'click', function(e) {
      p.infoWindow.close();
      if (infoWindowOptions.polygonOpen) {
        infoWindowOptions.polygonOpen(placemark, p.infoWindow, p.infoWindowOptions);
      }
      p.infoWindow.setOptions(p.infoWindowOptions);
      if (e && e.latLng) {
        p.infoWindow.setPosition(e.latLng);
      } else {
        p.infoWindow.setPosition(p.bounds.getCenter());
      }
      p.infoWindow.open(this.map);
    });
  }
  if (!!doc) doc.gpolygons.push(p);
  placemark.polygon = p;
  return p;
}

  return {
    // Expose some properties and methods

    options: parserOptions,
    docs:    docs,
    
    parse:          parse,
    render:         render,
    parseKmlString: parseKmlString,
    hideDocument:   hideDocument,
    showDocument:   showDocument,
    processStyles:  processStyles, 
    createMarker:   createMarker,
    createOverlay:  createOverlay,
    createPolyline: createPolyline,
    createPolygon:  createPolygon
  };
};
// End of KML Parser

// Helper objects and functions
geoXML3.getOpacity = function (kmlColor) {
  // Extract opacity encoded in a KML color value. Returns a number between 0 and 1.
  if (!!kmlColor &&
      (kmlColor !== '') &&
      (kmlColor.length == 8)) {
    var transparency = parseInt(kmlColor.substr(0, 2), 16);
    return transparency / 255;
  } else {
    return 1;
  }
};

// Log a message to the debugging console, if one exists
geoXML3.log = function(msg) {
  if (!!window.console) {
    console.log(msg);
  } else { alert("log:"+msg); }
};

// Combine two options objects: a set of default values and a set of override values 
geoXML3.combineOptions = function (overrides, defaults) {
  var result = {};
  if (!!overrides) {
    for (var prop in overrides) {
      if (overrides.hasOwnProperty(prop)) {
        result[prop] = overrides[prop];
      }
    }
  }
  if (!!defaults) {
    for (prop in defaults) {
      if (defaults.hasOwnProperty(prop) && (result[prop] === undefined)) {
        result[prop] = defaults[prop];
      }
    }
  }
  return result;
};

// Retrieve an XML document from url and pass it to callback as a DOM document
geoXML3.fetchers = [];

// parse text to XML doc
/**
 * Parses the given XML string and returns the parsed document in a
 * DOM data structure. This function will return an empty DOM node if
 * XML parsing is not supported in this browser.
 * @param {string} str XML string.
 * @return {Element|Document} DOM.
 */
geoXML3.xmlParse = function (str) {
  if ((typeof ActiveXObject != 'undefined') || ("ActiveXObject" in window)) {
    var doc = new ActiveXObject('Microsoft.XMLDOM');
    doc.loadXML(str);
    return doc;
  }

  if (typeof DOMParser != 'undefined') {
    return (new DOMParser()).parseFromString(str, 'text/xml');
  }

  return document.createElement('div', null);
}

// from http://stackoverflow.com/questions/11563554/how-do-i-detect-xml-parsing-errors-when-using-javascripts-domparser-in-a-cross
geoXML3.isParseError = function(parsedDocument) {
    if ((typeof ActiveXObject != 'undefined') || ("ActiveXObject" in window))
    return false;
    // parser and parsererrorNS could be cached on startup for efficiency
    var p = new DOMParser(),
        errorneousParse = p.parseFromString('<', 'text/xml'),
        parsererrorNS = errorneousParse.getElementsByTagName("parsererror")[0].namespaceURI;

    if (parsererrorNS === 'http://www.w3.org/1999/xhtml') {
        // In PhantomJS the parseerror element doesn't seem to have a special namespace, so we are just guessing here :(
        return parsedDocument.getElementsByTagName("parsererror").length > 0;
    }

    return parsedDocument.getElementsByTagNameNS(parsererrorNS, 'parsererror').length > 0;
};

geoXML3.fetchXML = function (url, callback) {
  function timeoutHandler() {
    geoXML3.log('XHR timeout');
    callback();
  };

  var xhrFetcher = new Object();
  if (!!geoXML3.fetchers.length) {
    xhrFetcher = geoXML3.fetchers.pop();
  } else {
    if (!!window.XMLHttpRequest) {
      xhrFetcher.fetcher = new window.XMLHttpRequest(); // Most browsers
    } else if (!!window.ActiveXObject) {
      xhrFetcher.fetcher = new window.ActiveXObject('Microsoft.XMLHTTP'); // Some IE
    }
  }

  if (!xhrFetcher.fetcher) {
    geoXML3.log('Unable to create XHR object');
    callback(null);
  } else {
      xhrFetcher.fetcher.open('GET', url, true);
      if (xhrFetcher.fetcher.overrideMimeType) {
        xhrFetcher.fetcher.overrideMimeType('text/xml');
      }
      xhrFetcher.fetcher.onreadystatechange = function () {
      if (xhrFetcher.fetcher.readyState === 4) {
        // Retrieval complete
        if (!!xhrFetcher.xhrtimeout)
          clearTimeout(xhrFetcher.xhrtimeout);
        if (xhrFetcher.fetcher.status >= 400) {
          geoXML3.log('HTTP error ' + xhrFetcher.fetcher.status + ' retrieving ' + url);
          callback();
        } else {
          // Returned successfully
          var xml = geoXML3.xmlParse(xhrFetcher.fetcher.responseText);
          if (xml.parseError && (xml.parseError.errorCode != 0)) {
           geoXML3.log("XML parse error "+xml.parseError.errorCode+", "+xml.parseError.reason+"\nLine:"+xml.parseError.line+", Position:"+xml.parseError.linepos+", srcText:"+xml.parseError.srcText);
           xml = "failed parse"
          } else if (geoXML3.isParseError(xml)) {
           geoXML3.log("XML parse error");
           xml = "failed parse"
          }
          callback(xml);
        }
        // We're done with this fetcher object
        geoXML3.fetchers.push(xhrFetcher);
      }
    };
    xhrFetcher.xhrtimeout = setTimeout(timeoutHandler, geoXML3.xhrTimeout);
    xhrFetcher.fetcher.send(null);
  }
};

//nodeValue: Extract the text value of a DOM node, with leading and trailing whitespace trimmed
geoXML3.nodeValue = function(node, defVal) {
  var retStr="";
  if (!node) {
    return (typeof defVal === 'undefined' || defVal === null) ? '' : defVal;
  }
   if(node.nodeType==3||node.nodeType==4||node.nodeType==2){
      retStr+=node.nodeValue;
   }else if(node.nodeType==1||node.nodeType==9||node.nodeType==11){
      for(var i=0;i<node.childNodes.length;++i){
         retStr+=arguments.callee(node.childNodes[i]);
      }
   }
   return retStr;
};

geoXML3.fetchZIP = function (url, callback, parser) {
  // Just need a single 'new' declaration with a really long function...
  var zipFile = new ZipFile(url, function (zip) {
    // Retrieval complete

    // Check for ERRORs in zip.status
    for (var i = 0; i < zip.status.length; i++) {
      var msg = zip.status[i];
      if (msg.indexOf("ERROR") == 0) {
        geoXML3.log('HTTP/ZIP error retrieving ' + url + ': ' + msg);
        callback();
        return;
      }
      else if (msg.indexOf("EXCEPTION") == 0) {  
        geoXML3.log('HTTP/ZIP exception retrieving ' + url + ': ' + msg);
        callback();
        return;
      } else if (msg.indexOf("WARNING") == 0) {  // non-fatal, but still might be useful
        geoXML3.log('HTTP/ZIP warning retrieving ' + url + ': ' + msg);
      } else if (msg.indexOf("INFO") == 0) {  // non-fatal, but still might be useful
        geoXML3.log('HTTP/ZIP info retrieving ' + url + ': ' + msg);
      }
    }

    // Make sure KMZ structure is according to spec (with a single KML file in the root dir)
    var KMLCount = 0;
    var KML;
    for (var i = 0; i < zip.entries.length; i++) {
      var name = zip.entries[i].name;
      if (!/\.kml$/.test(name)) continue;

      KMLCount++;
      if (KMLCount == 1) KML = i;
      else {
        geoXML3.log('KMZ warning retrieving ' + url + ': found extra KML "' + name + '" in KMZ; discarding...');
      }
    }

    // Returned successfully, but still needs extracting
    var baseUrl = cleanURL(defileURL(url), url) + '/';
    var kmlProcessing = {  // this is an object just so it gets passed properly
      timer: null,
      extractLeft: 0,
      timerCalls: 0
    };
    var extractCb = function(entry, entryContent) {
      var mdUrl = cleanURL(baseUrl, entry.name);
      var ext = entry.name.substring(entry.name.lastIndexOf(".") + 1).toLowerCase();
      kmlProcessing.extractLeft--;

      if ((typeof entryContent.description == "string") && (entryContent.name == "Error")) {
        geoXML3.log('KMZ error extracting ' + mdUrl + ': ' + entryContent.description);
        callback();
        return;
      }

      // MIME types that can be used in KML
      var mime;
      if (ext === 'jpg') ext = 'jpeg';
      if (/^(gif|jpeg|png)$/.test(ext)) mime = 'image/' + ext;
      else if (ext === 'mp3')           mime = 'audio/mpeg';
      else if (ext === 'm4a')           mime = 'audio/mp4';
      else if (ext === 'm4a')           mime = 'audio/MP4-LATM';
      else                              mime = 'application/octet-stream';

      parser.kmzMetaData[mdUrl] = {};
      parser.kmzMetaData[mdUrl].entry = entry;
      // data:image/gif;base64,R0lGODlhEAAOALMA...
      parser.kmzMetaData[mdUrl].dataUrl = 'data:' + mime + ';base64,' + base64Encode(entryContent);
      // IE cannot handle GET requests beyond 2071 characters, even if it's an inline image
  if (/msie/i.test(navigator.userAgent) && !/opera/i.test(navigator.userAgent))
        { 
            if (((IEversion() < 8.0) &&
                 (parser.kmzMetaData[mdUrl].dataUrl.length > 2071)) ||
                ((IEversion < 9.0) && 
                 (parser.kmzMetaData[mdUrl].dataUrl.length > 32767))) {
             parser.kmzMetaData[mdUrl].dataUrl =
             // this is a simple IE icon; to hint at the problem...
             'data:image/gif;base64,R0lGODlhDwAQAOMPADBPvSpQ1Dpoyz1p6FhwvU2A6ECP63CM04CWxYCk+V6x+UK++Jao3rvC3fj7+v///yH5BAEKAA8ALAAAAAAPABAAAASC8Mk5mwCAUMlWwcLRHEelLA' +
             'oGDMgzSsiyGCAhCETDPMh5XQCBwYBrNBIKWmg0MCQHj8MJU5IoroYCY6AAAgrDIbbQDGIK6DR5UPhlNo0JAlSUNAiDgH7eNAxEDWAKCQM2AAFheVxYAA0AIkFOJ1gBcQQaUQKKA5w7LpcEBwkJaKMUEQA7';
            } 
       }
       parser.kmzMetaData[internalSrc(entry.name)]=parser.kmzMetaData[mdUrl]; 

    };
    var kmlExtractCb = function(entry, entryContent) {
      if ((typeof entryContent.description == "string") && (entryContent.name == "Error")) {
        geoXML3.log('KMZ error extracting ' + entry.name + ': ' + entryContent.description);
        callback();
        return;
      }

      // check to see if the KML is the last file extracted
      clearTimeout(kmlProcessing.timer);
      if (kmlProcessing.extractLeft <= 1) {
        kmlProcessing.extractLeft--;
        callback(geoXML3.xmlParse(entryContent));
        return;
      }
      else {
        // KML file isn't last yet; it may need to use those files, so wait a bit (100ms)
        kmlProcessing.timerCalls++;
        if (kmlProcessing.timerCalls < 100) {
          kmlProcessing.timer = setTimeout(function() { kmlExtractCb(entry, entryContent); }, 100);
        }
        else {
          geoXML3.log('KMZ warning extracting ' + url + ': entire ZIP has not been extracted after 10 seconds; running through KML, anyway...');
          kmlProcessing.extractLeft--;
          callback(geoXML3.xmlParse(entryContent));
        }
      }
      return;
    };
    for (var i = 0; i < zip.entries.length; i++) {
      var entry = zip.entries[i];
      var ext = entry.name.substring(entry.name.lastIndexOf(".") + 1).toLowerCase();
      if (!/^(gif|jpe?g|png|kml)$/.test(ext)) continue;  // not going to bother to extract files we don't support
      if (ext === "kml" && i != KML)          continue;  // extra KMLs get discarded
      if (!parser && ext != "kml")            continue;  // cannot store images without a parser object

      // extract asynchronously
      kmlProcessing.extractLeft++;
      if (ext === "kml") entry.extract(kmlExtractCb);
      else               entry.extract(extractCb);
    }
  }); //,3 for most verbose logging

};

var toAbsURL = function (d, s) {
  var p, f, i;
  var h = location.protocol + "://" + location.host;

  if (!s.length)           return '';
  if (/^\w+:/.test(s))     return s;
  if (s.indexOf('/') == 0) return h + s;

  p = d.replace(/\/[^\/]*$/, '');
  f = s.match(/\.\.\//g);
  if (f) {
    s = s.substring(f.length * 3);
    for (i = f.length; i--;) { p = p.substring(0, p.lastIndexOf('/')); }
  }

  return h + p + '/' + s;
}

var dehostURL = function (s) {
  var h = location.protocol + "://" + location.host;
  h = h.replace(/([\.\\\+\*\?\[\^\]\$\(\)])/g, '\\$1');  // quotemeta
  return s.replace(new RegExp('^' + h, 'i'), '');
}

var cleanURL  = function (d, s) { return dehostURL(toAbsURL(d ? d.split('#')[0].split('?')[0] : defileURL(location.pathname), s ? s.split('#')[0].split('?')[0] : '')); }

var defileURL = function (s)    { return s ? s.substr(0, s.lastIndexOf('/') + 1) : '/'; }
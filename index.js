
function createRealtimeLayer(url, container) {
    return L.realtime(url, {
        interval: 3 * 1000,
        getFeatureId: function(f) {
            return f.properties.url;
        },
        cache: true,
        container: container,
        onEachFeature(f, l) {
            l.bindPopup(function() {
                return '<h3>' + f.properties.ip + '</h3>' +
                    '<p>' + new Date(f.properties.data) +
                    '<br/>Quality: <strong>' + f.properties.quality + '</strong></p>';
            });
        }
    });
}

var map = L.map('map'),
    clusterGroup = L.markerClusterGroup().addTo(map),
    subgroup1 = L.featureGroup.subGroup(clusterGroup),
    realtime1 = createRealtimeLayer('http://demo.gter.it/demo_rfi/punti.geojson', subgroup1).addTo(map);
	//realtime1 = createRealtimeLayer('https://earthquake.usgs.gov/earthquakes/feed/v1.0/summary/all_hour.geojson', subgroup1).addTo(map);


/*var map = L.map('map'),
    realtime = L.realtime('./geojson.php', {
        interval: 3 * 1000
    }).addTo(map);
*/


L.tileLayer('http://{s}.tile.osm.org/{z}/{x}/{y}.png', {
    attribution: '&copy; <a href="http://osm.org/copyright">OpenStreetMap</a> contributors',
     maxZoom: 22,
  maxNativeZoom: 19
    
}).addTo(map);


realtime1.once('update', function() {
    map.fitBounds(realtime1.getBounds(), {maxZoom: 20});
});
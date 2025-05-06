// Usage: showTunisiaChoroplethMap('mapDivId');

async function showTunisiaChoroplethMap(divId) {
    const mapDiv = document.getElementById(divId);
    if (!mapDiv) return;
    if (mapDiv._leaflet_id) {
        mapDiv._leaflet_id = null;
        mapDiv.innerHTML = "";
    }

    // 1. Create the map
    const map = L.map(divId, {
        zoomControl: true,
        attributionControl: false
    }).setView([34.0, 9.0], 6);

    // 2. Add tile layer
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors'
    }).addTo(map);

    // 3. Add blurry overlay (SVG rectangle covering the map)
    // Wait for the map to render, then overlay the blur
    setTimeout(() => {
        // Remove any previous overlay
        const old = mapDiv.querySelector('.leaflet-blur-overlay');
        if (old) old.remove();

        // Create SVG overlay
        const svg = document.createElementNS("http://www.w3.org/2000/svg", "svg");
        svg.setAttribute("class", "leaflet-blur-overlay");
        svg.setAttribute("width", "100%");
        svg.setAttribute("height", "100%");
        svg.setAttribute("viewBox", "0 0 800 400"); // 2:1 aspect ratio for 400px height

        // Draw a full rectangle
        const rect = document.createElementNS("http://www.w3.org/2000/svg", "rect");
        rect.setAttribute("x", "0");
        rect.setAttribute("y", "0");
        rect.setAttribute("width", "800");
        rect.setAttribute("height", "400");
        rect.setAttribute("fill", "#888");
        rect.setAttribute("opacity", "0.7");
        svg.appendChild(rect);

        // Add SVG overlay to map container
        mapDiv.appendChild(svg);
    }, 500);

    // 4. Load governorate counts and GeoJSON
    const countsResp = await fetch('get_gov_reservation_counts.php');
    const govCounts = await countsResp.json();

    const response = await fetch('tunisia-governorates.geojson');
    const geojson = await response.json();

    // 5. Draw Tunisia with choropleth coloring and popups
    function getColor(count) {
        if (count === 0) return '#e0e0e0'; // gray
        if (count < 5) return '#3498db';   // blue
        if (count < 10) return '#f1c40f';  // yellow
        return '#e74c3c';                  // red
    }

    function style(feature) {
        const name = feature.properties.gouv_fr;
        const count = govCounts[name] || 0;
        return {
            fillColor: getColor(count),
            weight: 2.5,
            opacity: 1,
            color: '#222',
            dashArray: '',
            fillOpacity: 0.95
        };
    }

    function highlightFeature(e) {
        var layer = e.target;
        layer.setStyle({
            weight: 4,
            color: '#c9a86c',
            dashArray: '',
            fillOpacity: 1
        });
        layer.bringToFront();
    }
    function resetHighlight(e) {
        geojsonLayer.resetStyle(e.target);
    }
    function onEachFeature(feature, layer) {
        const name = feature.properties.gouv_fr;
        const count = govCounts[name] || 0;
        layer.on({
            mouseover: function(e) {
                highlightFeature(e);
                layer.openPopup();
            },
            mouseout: function(e) {
                resetHighlight(e);
                layer.closePopup();
            },
            click: function(e) {
                layer.openPopup();
            }
        });
        layer.bindPopup(
            `<div style="font-size:1.1em;">
                <b>${name}</b><br>
                <span style="color:#c9a86c;">Départs: ${count}</span>
            </div>`
        );
    }

    // Draw Tunisia on top of the blur
    const geojsonLayer = L.geoJson(geojson, {
        style: style,
        onEachFeature: onEachFeature
    }).addTo(map);

    // Add scale and attribution
    L.control.scale().addTo(map);
    L.control.attribution({prefix: false}).addAttribution('© OpenStreetMap contributors').addTo(map);

    // Add legend
    addMapLegend();
}

function addMapLegend() {
    let legend = document.getElementById('map-legend');
    if (!legend) {
        legend = document.createElement('div');
        legend.id = 'map-legend';
        legend.style.marginTop = '18px';
        legend.style.textAlign = 'center';
        const mapDiv = document.getElementById('tunisiaChoroplethMap');
        if (mapDiv && mapDiv.parentNode) {
            mapDiv.parentNode.appendChild(legend);
        }
    }
    legend.innerHTML = `
      <span style="display:inline-block;width:22px;height:22px;background:#e0e0e0;border:1px solid #ccc;margin-right:6px;vertical-align:middle;"></span> 0 départs
      <span style="display:inline-block;width:22px;height:22px;background:#3498db;border:1px solid #ccc;margin-left:16px;margin-right:6px;vertical-align:middle;"></span> 1-4 départs
      <span style="display:inline-block;width:22px;height:22px;background:#f1c40f;border:1px solid #ccc;margin-left:16px;margin-right:6px;vertical-align:middle;"></span> 5-9 départs
      <span style="display:inline-block;width:22px;height:22px;background:#e74c3c;border:1px solid #ccc;margin-left:16px;margin-right:6px;vertical-align:middle;"></span> 10+ départs
    `;
} 
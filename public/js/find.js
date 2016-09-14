var vue = new Vue({
	el: '#find',
	data: {
		listToggle:false,
		mapToggle:true,
		islandToggle:false,
		distanceToggle:true,
		lat:0,
		lng:0,
		markers: [], 
		infoWindows: [], 
	},
	ready: function() {
		console.log("find");
		this.fetchFarmersMarket();

	},
	methods:{ 
		fetchFarmersMarket: function() {
			this.$http.get('/api/farmers_markets').then((response) => {
				this.$set('farmers_markets', response.data);
			}, (response) => {
				console.log(response);
			});
		},
		find: function(e){ 
			e.preventDefault(); 

		},
		onSubmitForm: function() {
			this.fetchNewFarmersMarkets();
		},
		onSubmitDistanceForm: function() {
			this.fetchNewFarmersMarketsByDistance();
		},
		fetchNewFarmersMarkets: function() {

			var paramsarray = [];
			if(this.island) {
				paramsarray.push({island: this.island})
			}
			if(this.day) {
				paramsarray.push({island: this.island})
			}
			if(this.time_hour) {
			}
			this.$http.get('api/farmers_markets/queried', {params: {island: this.island, day: this.day}}).then((response) => {
				this.farmers_markets = [];
				this.farmers_markets = response.data;
			}, (response) => {
				console.log("error");
			});

			this.deleteMarkers();
			for(var i = 0;  i < this.farmers_markets.length;  i++) { 
				var contentString = '<div id="content">'+
	            '<div id="siteNotice">'+
	            '</div>'+
	            '<h3 id="firstHeading" class="firstHeading">' + 
	            '<a href="/farmers_market/' + vue.farmers_markets[i].id + '">' + vue.farmers_markets[i].farmers_market_name + '</a>'+
	            '</h3>'+
	            '</div>'+
	            '</div>';

				var infowindow = new google.maps.InfoWindow({
		          content: contentString
		        });
				var latlng = new google.maps.LatLng(vue.farmers_markets[i].lat, vue.farmers_markets[i].lng);
				var marker = new google.maps.Marker({
					map: this.map,
					position: latlng
				});
				this.bindInfoWindow(marker, this.map, infowindow);
				vue.markers.push(marker);
			}
			this.map.setCenter(this.findMiddleOfCoordinates(this.markers));
		},
		bindInfoWindow: function(marker, map, infowindow) {
		    marker.addListener('click', function() {
		        infowindow.open(map, this);
		    });
		},
		setMapOnAll: function(map) {
			for( var i = 0; i < this.markers.length; i++) {
				this.markers[i].setMap(map);
			}
		},
		clearMarkers: function() {
			this.setMapOnAll(null);
		},
		deleteMarkers: function() {
			this.clearMarkers();
			this.markers =[];
			this.infoWindows = [];
		},
		listTemplate: function() {
			console.log('list')
			this.listToggle = true;
			this.mapToggle = false;
		},
		mapTemplate: function() {
			console.log('map')
			this.listToggle = false;
			this.mapToggle = true;
		},
		islandTemplate: function() {
			this.islandToggle = true;
			this.distanceToggle = false;
		},
		distanceTemplate: function() {
			this.islandToggle = false;
			this.distanceToggle = true;
		},
		initialize: function() {
			this.$set('geocoder', new google.maps.Geocoder());
			var latlng = new google.maps.LatLng(12, 13);
			var mapOptions = {
				zoom: 12,
				center: latlng,
			}
			this.$set('map', new google.maps.Map(document.getElementById('map'), mapOptions));
		},
		geocodeAddress: function(geocoder, resultsMap) {
			geocoder.geocode(
				{'address': this.location}, 
				function(results, status) {
					if (status === 'OK') {
						resultsMap.setCenter(results[0].geometry.location);
						vue.deleteMarkers();
						var marker = new google.maps.Marker({
							map: resultsMap,
							position: results[0].geometry.location
						});
						vue.markers.push(marker);
							vue.lat = results[0].geometry.location.lat();
							console.log(vue.lat);
							vue.lng = results[0].geometry.location.lng();
							console.log(vue.lng);
							vue.$http.get('api/farmers_markets/getByLocation', {params: {lat: vue.lat, lng: vue.lng}}).then((response) => {
							vue.farmers_markets = [];
							vue.farmers_markets = response.data;
						}, (response) => {
							console.log("error");
						});
						vue.deleteMarkers();
						for($i = 0; $i < vue.farmers_markets.length; $i++) { 
							var contentString = '<div id="content">'+
				            '<div id="siteNotice">'+
				            '</div>'+
				            '<h3 id="firstHeading" class="firstHeading">' + 
				            '<a href="/farmers_market/' + vue.farmers_markets[$i].id + '">' + vue.farmers_markets[$i].farmers_market_name + '</a>'+
				            '</h3>'+
				            '</div>'+
				            '</div>';

							var infowindow = new google.maps.InfoWindow({
					          content: contentString
					        });
					        vue.infoWindows.push(infowindow);
							var latlng = new google.maps.LatLng(vue.farmers_markets[$i].lat, vue.farmers_markets[$i].lng);
							var marker = new google.maps.Marker({
								map: vue.map,
								position: latlng
							});
							marker.addListener('click', function() {
								vue.infoWindows[$i].open(vue.map, marker);
								console.log("asdf");
							});
							vue.markers.push(marker);
						}
						vue.map.setCenter(vue.findMiddleOfCoordinates(vue.markers));
					} else {
						alert('Geocode was not successful for the following reason: ' + status);
					}
				}
			)
		},
		findMiddleOfCoordinates: function(markers) {
			var x= 0;
			var y= 0;
			var z= 0;
			console.log(markers.length);
			for(var i = 0; i < markers.length; i++){ 
				var lat = markers[i].getPosition().lat() * Math.PI /180;
				var lng = markers[i].getPosition().lng() * Math.PI /180;
				
				x += Math.cos(lat) * Math.cos(lng);
	            y += Math.cos(lat) * Math.sin(lng);
	            z += Math.sin(lat);

				var total = markers.length;

		        x = x / total;
		        y = y / total;
		        z = z / total;

		        var centralLongitude = Math.atan2(y, x);
		        var centralSquareRoot = Math.sqrt(x * x + y * y);
		        var centralLatitude = Math.atan2(z, centralSquareRoot);

		        return new google.maps.LatLng(centralLatitude * 180 / Math.PI, centralLongitude * 180 / Math.PI);
			}
		}
	}
})

function initialize() {
	vue.initialize();
}
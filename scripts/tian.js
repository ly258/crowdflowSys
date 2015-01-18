OpenLayers.Layer.TiandituLayer = OpenLayers.Class(OpenLayers.Layer.Grid, {

	mapType: null,
	mirrorUrls: null,
	topLevel: null,
	bottomLevel: null,

	topTileFromX: -180,
	topTileFromY: 90,
	topTileToX: 180,
	topTileToY: -270,
    type:"vec_c",
	isBaseLayer: true,

	initialize: function (name,type) {

		var url = "http://t1.tianditu.cn/DataServer";
		var options = new Object();
		options.topLevel = 0;
		options.bottomLevel = 18;
		options.maxResolution = this.getResolutionForLevel(options.topLevel);
		options.minResolution = this.getResolutionForLevel(options.bottomLevel);
		options.maxExtent = new OpenLayers.Bounds(-180, -90, 
				            180, 90);
		this.type=type;

		var newArguments = [name, url, {}, options];
		OpenLayers.Layer.Grid.prototype.initialize.apply(this, newArguments);
		},

		clone: function (obj) {

		if (obj == null) {
		obj = new OpenLayers.Layer.TDTLayer(this.name, this.url, this.options);
		}

		obj = OpenLayers.Layer.Grid.prototype.clone.apply(this, [obj]);

		return obj;
	},

	getURL: function (bounds) {
		var level = this.getLevelForResolution(this.map.getResolution());  
        var coef = 360 / Math.pow(2, level); 
		var x_num = this.topTileFromX < this.topTileToX ? Math  
                        .round((bounds.left - this.topTileFromX) / coef) : Math  
                        .round((this.topTileFromX - bounds.right) / coef);  
        var y_num = this.topTileFromY < this.topTileToY ? Math  
                        .round((bounds.bottom - this.topTileFromY) / coef)  
                        : Math.round((this.topTileFromY - bounds.top) / coef);

		var type = this.type;

	    var url = this.url;
	    if (this.mirrorUrls != null) {
			url = this.selectUrl(x_num, this.mirrorUrls);
		}

		return this.getFullRequestString({ T: type, x: x_num, y: y_num, l: level }, url);
	},
	selectUrl: function (a, b) { return b[a % b.length] },
	getLevelForResolution: function (res) {
			var ratio = this.getMaxResolution() / res;
			if (ratio < 1) return 0;
			for (var level = 0; ratio / 2 >= 1; )
			{ level++; ratio /= 2; }
			return level;
		},
	getResolutionForLevel: function (level) {
		return 360 / 256 / Math.pow(2, level);
	},
	getMaxResolution: function () {
		return this.getResolutionForLevel(this.topLevel);
	},
	getMinResolution: function () {
		return this.getResolutionForLevel(this.bottomLevel);
	},
	addTile: function (bounds, position) {
		var url = this.getURL(bounds);
		var img = new OpenLayers.Tile.Image(this, position, bounds, url, this.tileSize);
		img.onImageError = function(){
			var img = this.imgDiv;
			if (img.src != null) {
				this.imageReloadAttempts++;
				if (this.imageReloadAttempts <= OpenLayers.IMAGE_RELOAD_ATTEMPTS) {
					this.setImgSrc(this.layer.getURL(this.bounds));
				} else {
					OpenLayers.Element.addClass(img, "olImageLoadError");
					this.events.triggerEvent("loaderror");
				}
			}
		};
		return img;
	},	

	CLASS_NAME: "OpenLayers.Layer.TiandituLayer"
});

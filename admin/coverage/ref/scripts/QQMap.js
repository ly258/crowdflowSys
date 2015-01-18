OpenLayers.Layer.QQMap = OpenLayers.Class(OpenLayers.Layer.Grid, {	
	isBaseLayer: true,
	initialize: function (name) {
		var url = "http://p0.map.gtimg.com/maptilesv3/";
		var options = new Object();
		options.topLevel = 0;
		options.bottomLevel = 18;
		options.maxResolution = this.getResolutionForLevel(options.topLevel);
		options.minResolution = this.getResolutionForLevel(options.bottomLevel);
		options.maxExtent = new OpenLayers.Bounds(-20037508.3427892, -20037508.3427892, 
				            20037508.3427892, 20037508.3427892),
		options.mirrorUrls = ["http://p0.map.gtimg.com/maptilesv3/",
				    "http://p1.map.gtimg.com/maptilesv3/",
				    "http://p2.map.gtimg.com/maptilesv3/",
				    "http://p3.map.gtimg.com/maptilesv3/"];

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
        	var res = this.map.getResolution();
        	var bbox = this.map.getMaxExtent();
        	var size = this.tileSize;
        	var tileZ = this.map.zoom;
		var level = this.getLevelForResolution(this.map.getResolution());
		
        	//计算列号
        	var tileX = Math.round((bounds.left - bbox.left) / (res * size.w));
        	//计算行号
        	var tileY = Math.round((bounds.top - bbox.bottom) / (res * size.h));
		var imgformat=this.sateTiles ? ".jpg" : ".png";
		
        	var tileNo =  tileZ + "/" + Math.floor(tileX / 16) + "/" +  Math.floor(tileY / 16)+ "/" + tileX + "_" + tileY + imgformat;
		
        	var Surl=this.url;
        	if (OpenLayers.Util.isArray(Surl))
			Surl = this.selectUrl(tileNo, Surl);
        	return Surl+tileNo;
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
		return 20037508.3427892 * 2 / 256 / Math.pow(2, level);
	},
	getMaxResolution: function () {
		return this.getResolutionForLevel(this.topLevelIndex)
	},
	getMinResolution: function () {
		return this.getResolutionForLevel(this.bottomLevelIndex)
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
		}
		return img;
	},	
    
    CLASS_NAME: "OpenLayers.Layer.QQMap"
});

/* globals jQuery, packery, sowb */

var sowb = window.sowb || {};

jQuery( function ( $ ) {
	sowb.setupSimpleMasonries = function () {
		var $grid = $( '.sow-masonry-grid' );
		
		if ( $grid.data( 'initialized' ) ) {
			return $grid;
		}
		
		var resizeMasonry = function () {
			$grid.each( function () {
				var $gridEl = $( this );
				var layouts = $gridEl.data( 'layouts' );
				var tabletQuery = window.matchMedia( '(max-width: ' + layouts.tablet.breakPoint + 'px)' );
				var mobileQuery = window.matchMedia( '(max-width: ' + layouts.mobile.breakPoint + 'px)' );
				var layout = layouts.desktop;
				if ( mobileQuery.matches ) {
					layout = layouts.mobile;
				} else if ( tabletQuery.matches ) {
					layout = layouts.tablet;
				}
				var numColumns = layout.numColumns;
				$gridEl.css( 'width', 'auto' );
				var horizontalGutterSpace = layout.gutter * ( numColumns - 1 );
				var columnWidth = ( $gridEl.width() - ( horizontalGutterSpace ) ) / numColumns;
				$gridEl.width( ( columnWidth * numColumns ) + horizontalGutterSpace );
				
				$gridEl.imagesLoaded( function () {
					$gridEl.find( '> .sow-masonry-grid-item' ).each( function () {
						var $$ = $( this );
						var colSpan = $$.data( 'colSpan' );
						colSpan = Math.max( Math.min( colSpan, layout.numColumns ), 1 );
						$$.width( ( columnWidth * colSpan ) + ( layout.gutter * ( colSpan - 1 ) ) );
						var rowSpan = $$.data( 'rowSpan' );
						rowSpan = Math.max( Math.min( rowSpan, layout.numColumns ), 1 );
						//Use rowHeight if non-zero else fall back to matching columnWidth.
						var rowHeight = layout.rowHeight || columnWidth;
						$$.css( 'height', ( rowHeight * rowSpan ) + ( layout.gutter * ( rowSpan - 1 ) ) );
						
						var $img = $$.find( '> img,> a > img' );
						var imgAR = $img.attr( 'height' ) > 0 ? $img.attr( 'width' ) / $img.attr( 'height' ) : 1;
						var itemAR = $$.height() > 0 ? $$.width() / $$.height() : 1;
						imgAR = parseFloat( imgAR.toFixed( 3 ) );
						itemAR = parseFloat( itemAR.toFixed( 3 ) );
						if ( imgAR > itemAR ) {
							$img.css( 'width', 'auto' );
							$img.css( 'height', '100%' );
							$img.css( 'margin-top', '' );
							var marginLeft = ( $img.width() - $$.width() ) * -0.5;
							$img.css( 'margin-left', marginLeft + 'px' );
						}
						else {
							$img.css( 'height', 'auto' );
							$img.css( 'width', '100%' );
							$img.css( 'margin-left', '' );
							var marginTop = ( $img.height() - $$.height() ) * -0.5;
							$img.css( 'margin-top', marginTop + 'px' );
						}
					} );

					$gridEl.packery( {
						itemSelector: '.sow-masonry-grid-item',
						columnWidth: columnWidth,
						gutter: layout.gutter,
						originLeft: $gridEl.data( 'layout-origin-left' ),
					} );

					// If preloader is present, remove and show masonry
					if ( $grid.prev( '.sow-masonry-grid-preloader' ).length ) {
						$grid.prev().remove()
						$grid.css( 'opacity', 1 );
					}
				} );
			} );
		};
		
		$( window ).on( 'resize panelsStretchRows', resizeMasonry );
		
		// Ensure that the masonry has resized correctly on load.
		setTimeout( function () {
			resizeMasonry();
		}, 100 );
		
		$grid.data( 'initialized', true );
	};
	sowb.setupSimpleMasonries();
	
	$( sowb ).on( 'setup_widgets', sowb.setupSimpleMasonries );
} );

window.sowb = sowb;

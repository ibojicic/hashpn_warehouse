/*
$(document).ready(function(){
    $('.box').hide();
    $('a').click(function() {
        $('.box').hide();
        $('#show' +  $(this).attr("id")).show('slow');

    });
});

function toggleStatus() {
    if ($('#toggleElement').is(':checked')) {
        $('#elementsToOperateOn :input').removeAttr('disabled');
    } else {
        $('#elementsToOperateOn :input').attr('disabled', true);
        $('#elementsToOperateOn :input').val('');
    }
}
*/

jQuery(document).ready(function($) {
    // We only want these styles applied when javascript is enabled
    $('div.navigation').css({
        'width' : 'auto',
        'min-width' : '400px',
        'max-width' : '600px',
        'float' : 'right'
    });
    
    // Initially set opacity on thumbs and add
    // additional styling for hover effect on thumbs
    var onMouseOutOpacity = 1.00;
    $('#thumbs ul.thumbs li').opacityrollover({
        mouseOutOpacity:   onMouseOutOpacity,
        mouseOverOpacity:  1.0,
        fadeSpeed:         'fast',
        exemptionSelector: '.selected'
    });

    // Initialize Advanced Galleriffic Gallery
    var gallery = $('#thumbs').galleriffic({
        delay:                     0,
        numThumbs:                 8,
        preloadAhead:              2,
        enableTopPager:            false,
        enableBottomPager:         true,
        maxPagesToShow:            7,
        imageContainerSel:         '#slideshow',
        controlsContainerSel:      '#controls',
        captionContainerSel:       '#caption',
        loadingContainerSel:       '#loading',
        renderSSControls:          true,
        renderNavControls:         false,
        playLinkText:              'Play Slideshow',
        pauseLinkText:             'Pause Slideshow',
        prevLinkText:              '&lsaquo; Previous Photo',
        nextLinkText:              'Next Photo &rsaquo;',
        nextPageLinkText:          'Next &rsaquo;',
        prevPageLinkText:          '&lsaquo; Prev',
        enableHistory:             false,
        enableKeyboardNavigation:  true, // Specifies whether keyboard navigation is enabled
        autoStart:                 false,
        syncTransitions:           true,
        defaultTransitionDuration: 200,
        onSlideChange:             function(prevIndex, nextIndex) {
            // 'this' refers to the gallery, which is an extension of $('#thumbs')
            this.find('ul.thumbs').children()
            .eq(prevIndex).fadeTo('fast', onMouseOutOpacity).end()
            .eq(nextIndex).fadeTo('fast', 1.0);
        },
        onPageTransitionOut:       function(callback) {
            this.fadeTo('fast', 0.0, callback);
        },
        onPageTransitionIn:        function() {
            this.fadeTo('fast', 1.0);
        }
    });
});
/**
 * jQuery Opacity Rollover plugin
 *
 * Copyright (c) 2009 Trent Foley (http://trentacular.com)
 * Licensed under the MIT License:
 *   http://www.opensource.org/licenses/mit-license.php
 */
(function($) {
    var defaults = {
        mouseOutOpacity:   0.8,
        mouseOverOpacity:  1.0,
        fadeSpeed:         'fast',
        exemptionSelector: '.selected'
    };

    $.fn.opacityrollover = function(settings) {
        // Initialize the effect
        $.extend(this, defaults, settings);

        var config = this;

        function fadeTo(element, opacity) {
            var $target = $(element);

            if (config.exemptionSelector)
                $target = $target.not(config.exemptionSelector);

            $target.fadeTo(config.fadeSpeed, opacity);
        }

        this.css('opacity', this.mouseOutOpacity)
        .hover(
            function () {
                fadeTo(this, config.mouseOverOpacity);
            },
            function () {
                fadeTo(this, config.mouseOutOpacity);
            });

        return this;
    };
})(jQuery);


/*
$(document).ready(function(){
  $("#toggleoverlay").change(function(){
    if (this.checked) {
        $("#imgoverlay").show();
    } else {
        $("#imgoverlay").hide();
    }
  });
  */
$(document).ready(function(){
  $("#toggle_centroid").change(function(){
    if (this.checked) {
        $("#centroid_overlay").show();
    } else {
        $("#centroid_overlay").hide();
    }
  });
});


$(document).ready(function(){
  $("#toggle_CS_pos").change(function(){
    if (this.checked) {
        $("#CS_pos_overlay").show();
    } else {
        $("#CS_pos_overlay").hide();
    }
  });
});

$(document).ready(function(){
  $("#toggle_diameter").change(function(){
    if (this.checked) {
        $("#diameter_overlay").show();
    } else {
        $("#diameter_overlay").hide();
    }
  });
});

$(".selector").validate({
  debug: true
});


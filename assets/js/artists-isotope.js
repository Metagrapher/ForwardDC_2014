jQuery(document).ready(function() {
    imagesLoaded('.js-isotope', function() {
        $container = jQuery('.js-isotope').isotope();

        // store filter for each group
        var filters = {};
        jQuery(".filter_dropdown").change(function() {
            var $this = jQuery(this).children(":selected");
            console.log($this);
            var $filterGroup = $this.parents('.filterGroup');
            var filterGroup = $filterGroup.attr('data-filter-group');

            filters[filterGroup] = $this.attr('value');
            console.log('filter: ', filters[filterGroup]);

            var filterValue = '';
            for (var prop in filters) {
                filterValue += filters[prop];
            }

            console.log("filterValue:", filterValue);

            $container.isotope({
                filter: filterValue
            });
        });

        jQuery('article.isotope-ready').click(function() {
            jQuery(this).toggleClass("selected");
        });

        jQuery(window).resize(function() {
            jQuery("article.isotope-ready").each(function() {
                var $this = jQuery(this);
                $this.css({
                    "height": $this.width() + "px"
                });
            });
        });
    });
});
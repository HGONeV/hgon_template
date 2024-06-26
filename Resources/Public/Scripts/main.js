// we have to call the helllicht functions from /hgon-html/site/snippets/foot.php
$('.js-navbar').helllnav();
$('.js-slider').helllslider();
$('.js-lightbox').helllbox();
$('.js-open-mdl').helllmodal();
$('.js-tabs-container').hellltabs({
  tabIndex: 0
});

/* copy to clipboard function for tp-single share function */
function copyToClipboard() {
    /* Get the text field */
    var copyText = document.getElementById("hgon-url");

    /* Select the text field */
    copyText.select();

    /* Copy the text inside the text field */
    document.execCommand("copy");

    /* Alert the copied text */
    jQuery(".clipboard-container").show().delay(5000).fadeOut(400);

    jQuery('.cb-close').bind('click', function(){
        jQuery('.clipboard-container').hide();
    });
}


jQuery(document).ready(function(){

    // We don't use the ajax api while we're in lightbox content (does not work, because featherlight creates a copy)
    jQuery(document).on('submit', '#rkwevents-reservation-form', function (event) {
        jQuery.ajax
        ({
             url: jQuery(this).attr('action'),
             data: jQuery(this).serialize(),
             type: 'post',
             success: function (result) {
                 jQuery('.featherlight-content #rkwevents-reservation-form').html(result);
                 jQuery(".featherlight-content").scrollTop(0);
             }
         });
        event.preventDefault();
    });


    // hide events more button after click. If there are further entries, a new button is created by the ajax loaded template
    jQuery(document).on('click', 'a.next-page.autoload', function (event) {
       jQuery(this).parent('div.align-center').parent('div.grid').remove();
    });


    jQuery('.link--topanchor').click(function(e) {
        $('body,html').animate({
           scrollTop: 0
       }, 800);
        e.preventDefault();
    });


    // HANDLE IT THAT THE FORM IN THE POPUP IS ONLY A COPY. HAS IDENTIFIER ISSUES ON SELECT CHECKOXES AND RADIOS
    /*
    jQuery('.btn.btn--primary.btn--rounded').featherlight({

        // using featherlight functions leads to some issues on send via ajax, objects and what ever...

        filter: jQuery(document).on('submit', '#rkwevents-reservation-form'),
        afterOpen: function(event){
            jQuery(document).find('.main__content #rkw-events-reservation-plugin.featherlight-form-container label.checkbox, .main__content #rkw-events-reservation-plugin.featherlight-form-container label.radio').each(function (index) {
                var newIdentifier = 'custom-identifier' + index + Date.now();

                // first get it and save it
                var oldIdentifier = jQuery(this).attr('for');
                jQuery(this).attr('data-for', oldIdentifier);

                // override it
                jQuery(this).attr('for', newIdentifier);
                jQuery(this).find('label').attr('for', newIdentifier);
                jQuery(this).find('input.input').attr('id', newIdentifier);
            });
        },

         afterClose: function(event){
             jQuery(document).find('.main__content #rkw-events-reservation-plugin.featherlight-form-container label.checkbox, .main__content #rkw-events-reservation-plugin.featherlight-form-container label.radio').each(function (index) {

                 // first get it and save it
                 var oldIdentifier = jQuery(this).attr('data-for');

                 // override it
                 jQuery(this).attr('for', oldIdentifier);
                 jQuery(this).find('label').attr('for', oldIdentifier);
                 jQuery(this).find('input.input').attr('id', oldIdentifier);
             });
         }
      });
    */


    // Featherlight Lightbox fix for duplicated html ID elements
    jQuery(document).on('DOMNodeInserted', '.featherlight-content #rkw-events-reservation-plugin', function (e) {

        jQuery(document).find('.main__content #rkw-events-reservation-plugin.featherlight-form-container label.checkbox, .main__content #rkw-events-reservation-plugin.featherlight-form-container label.radio').each(function (index) {
            var newIdentifier = 'custom-identifier' + index + Date.now();

            // first get it and save it
            var oldIdentifier = jQuery(this).attr('for');
            jQuery(this).attr('data-for', oldIdentifier);

            // override it
            jQuery(this).attr('for', newIdentifier);
            jQuery(this).find('label').attr('for', newIdentifier);
            jQuery(this).find('input.input').attr('id', newIdentifier);
        });

    });

    jQuery(document).on('DOMNodeRemoved', '.featherlight-content #rkw-events-reservation-plugin', function (e) {

        jQuery(document).find('.main__content #rkw-events-reservation-plugin.featherlight-form-container label.checkbox, .main__content #rkw-events-reservation-plugin.featherlight-form-container label.radio').each(function (index) {

            // first get it and save it
            var oldIdentifier = jQuery(this).attr('data-for');

            // override it
            jQuery(this).attr('for', oldIdentifier);
            jQuery(this).find('label').attr('for', oldIdentifier);
            jQuery(this).find('input.input').attr('id', oldIdentifier);
        });

    });

});


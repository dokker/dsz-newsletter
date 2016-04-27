jQuery(document).ready(function($) {
 
  // initialize meta boxes
  postboxes.add_postbox_toggles(pagenow);

  // initialize media selector
  $('#upload_image_button').click(function() {
    // https://github.com/thehappybit/THB-WordPress-Media-Selector
    var media = new THB_MediaSelector( {
      select: function( selected_images ) {
        $('#upload_image').val(selected_images.url);
      }
    } );
    media.open();
  });
});

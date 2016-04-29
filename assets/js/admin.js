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

  function messageDialog (title, message) {
    // modal = '<div id="message_dialog" title="' + title + '"><p>' + message + '</p></div>';
    // $('#message-dialog').remove();
    // $('body').append(modal);
    // $('#message-dialog').dialog();
    alert(message);
  }

  /**
   * sortable lists
   */
  $('.nl-sortable').each(function () {
    $(this).sortable({
      axis: 'y',
      update: function (event, ui) {
      }
    });
  });

  // handle submit
  $('.nl-form').submit(function() {
    var data = $('.nl-sortable.recommendations').sortable('serialize');
    $('.input-recommendations').val(data);
  });

  /**
   * Find match in given list
   * @param  {object} $list List object
   * @param  {int} id    ID to search for
   * @return {boolean}       Match
   */
  function findInList ($list, id) {
    var match = false;
    $list.children().each(function() {
      if ($(this).data('id') == parseInt(id)) {
        match = true;
      }
    });
    return match;
  }

  /**
   * Handles choices from lists
   * @param  {object} button Button object
   * @param  {object} list   List object
   */
  function handle_choice($button, $list) {
    $button.click(function () {
      id = $(this).prev().val();
      if (!findInList($list, id)) {
        label = $(this).prev().find('option:selected').text();
        var item = '<li id="item_' + id + '" data-id="' + id + '">' + label + '</li>';
        $list.append(item);
      } else {
        messageDialog('Ütközés', 'Ez az előadás már szerepel a listában.');
      }
    });
  }

  /**
   * Initialize the newsletter generation form
   */
  function init_nl_form() {
    handle_choice($('.add_recommendation'), $('.nl-sortable.recommendations'));
  }

  init_nl_form();
});

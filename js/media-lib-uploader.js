jQuery(document).ready(function($){
  $('#my5-box-img1').click(function(e) {
	var mediaUploader1;
    e.preventDefault();
    // If the uploader object has already been created, reopen the dialog
      if (mediaUploader1) {
      mediaUploader1.open();
      return;
    }
    // Extend the wp.media object
    mediaUploader1 = wp.media.frames.file_frame = wp.media({
      title: 'Choose Image',
      button: {
      text: 'Choose Image'
    }, multiple: true });

    // When a file is selected, grab the URL and set it as the text field's value
    mediaUploader1.on('select', function() {
      var attachment = mediaUploader1.state().get('selection').first().toJSON();
	  //alert(JSON.stringify(attachment));
	  var src = '<img src="' + attachment.url + '" style="width:100%; height:auto;">';
      $('#my5-box-img1').html(src);
	  $('#my5tech_extra_image_2').val(attachment.id);
	  $('.hide-if-no-image').css('display', 'block');
	  $('.howto2').css('display', 'block');
    });
    // Open the uploader dialog
    mediaUploader1.open();
  });
});
(function ($) {
    $(function () {
        var mediaFrame;
        var imageId = $('#morshaus-event-admin-image-id');
        var preview = $('#morshaus-event-admin-image-preview');
        var removeButton = $('#morshaus-event-admin-remove-image');

        $('#morshaus-event-admin-select-image').on('click', function (event) {
            event.preventDefault();

            if (mediaFrame) {
                mediaFrame.open();
                return;
            }

            mediaFrame = wp.media({
                title: 'Bild für die Terminkarte auswählen',
                button: {
                    text: 'Bild übernehmen'
                },
                library: {
                    type: 'image'
                },
                multiple: false
            });

            mediaFrame.on('select', function () {
                var attachment = mediaFrame.state().get('selection').first().toJSON();
                var imageUrl = attachment.sizes && attachment.sizes.medium
                    ? attachment.sizes.medium.url
                    : attachment.url;
                var image = $('<img>', {
                    src: imageUrl,
                    alt: ''
                });

                imageId.val(attachment.id);
                preview.empty().append(image);
                removeButton.prop('disabled', false);
            });

            mediaFrame.open();
        });

        removeButton.on('click', function (event) {
            event.preventDefault();
            imageId.val('');
            preview.empty().append(
                $('<p>').text('Noch kein eigenes Bild ausgewählt. Ohne eigenes Bild wird das Kategorienbild verwendet.')
            );
            removeButton.prop('disabled', true);
        });
    });
})(jQuery);

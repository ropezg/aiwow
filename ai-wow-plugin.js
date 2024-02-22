jQuery(document).ready(function($) {
    // Remove any existing click events to avoid multiple bindings
    $('#ai-wow-plugin-generate').off('click').on('click', function() {
    console.log('Button clicked');
        var $button = $(this); // Cache the button
        var selectedPost = $('#ai-wow-plugin-form input[type="radio"]:checked').val();
        
        // Check if a post is selected
        if (!selectedPost) {
            alert('Please select a post.');
            return;
        }
        
        // Disable the button to prevent multiple clicks
        $button.prop('disabled', true).text('Generating...');

        var postData = {
            'action': 'generate_key_takeaways',
            'post_id': selectedPost,
            'nonce': ai_wow_plugin_ajax.nonce
        };

        $.ajax({
            url: ai_wow_plugin_ajax.ajax_url,
            type: 'POST',
            data: postData,
            success: function(response) {
                if (response.success) {
                    alert('Key Takeaway generated: ' + response.data.message);
                } else {
                    alert('Error: ' + response.data.message);
                }
                // Re-enable the button after the AJAX call completes
                $button.prop('disabled', false).text('Generate Key Takeaway');
            },
            error: function() {
                alert('An error occurred while generating the key takeaway.');
                // Re-enable the button if there's an AJAX error
                $button.prop('disabled', false).text('Generate Key Takeaway');
            }
        });
    });
});

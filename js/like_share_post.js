// js/like_share_post.js

// Function to handle the like functionality
function likePost(postId) {
    // Send an AJAX request to the server to like the post
    $.ajax({
        url: 'like_post.php', // Replace 'like_post.php' with the actual server-side script that handles the like functionality
        method: 'POST',
        data: { post_id: postId },
        success: function(response) {
            // If the server responds successfully, update the UI to reflect the change
            if (response === 'success') {
                // For example, you can change the appearance of the like link
                $('#like-link-' + postId).addClass('liked');
            }
        },
        error: function() {
            // Handle errors, if any
            console.log('Error occurred during the AJAX request.');
        }
    });
}

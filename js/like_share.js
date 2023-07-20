// Like button click event
$(".like-button").click(function() {
    var postId = $(this).data("post-id");

    // Send AJAX request to like_post.php
    $.ajax({
        url: "like_post.php",
        method: "POST",
        data: { post_id: postId },
        success: function(response) {
            // Update the like count on the page
            var likesCountElement = $(".likes-count[data-post-id='" + postId + "']");
            likesCountElement.text(response.likes_count + " Likes");
        },
        error: function(xhr, status, error) {
            // Handle error
            console.error(error);
        }
    });
});

// Share button click event
$(".share-button").click(function() {
    var postId = $(this).data("post-id");

    // Send AJAX request to share_post.php
    $.ajax({
        url: "share_post.php",
        method: "POST",
        data: { post_id: postId },
        success: function(response) {
            // Update the share count on the page
            var sharesCountElement = $(".shares-count[data-post-id='" + postId + "']");
            sharesCountElement.text(response.shares_count + " Shares");
        },
        error: function(xhr, status, error) {
            // Handle error
            console.error(error);
        }
    });
});

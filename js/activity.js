function fetchActivities() {
    // Make an AJAX request to fetch the activities from the server
    $.ajax({
        url: 'activity_fetch.php',
        method: 'GET',
        dataType: 'json',
        success: function (data) {
            // Update the activity list with the new data
            var activityList = $('#activityList');
            activityList.empty(); // Clear the previous content
            if (data.length > 0) {
                $.each(data, function (index, activity) {
                    // Append the activity to the list
                    activityList.append(
                        '<div class="activity-item">' +
                        '<strong>' + activity.activity_type + ':</strong> ' +
                        activity.activity_details + ' ' +
                        '<span class="text-muted">(' + activity.activity_date + ')</span>' +
                        '</div>'
                    );
                });
            } else {
                // No recent activity
                activityList.append('<p>No recent activity</p>');
            }
        },
        error: function (xhr, status, error) {
            // Handle error, if any
            console.log('Error fetching activities:', error);
        }
    });
}

// Fetch activities initially on page load
fetchActivities();

// Fetch activities every 30 seconds (adjust the interval as needed)
setInterval(fetchActivities, 30000);

DELIMITER //
CREATE TRIGGER post_notification AFTER INSERT ON posts
FOR EACH ROW
BEGIN
    -- Insert a notification for the post
    INSERT INTO notifications (type, source_id, user_id)
    VALUES ('post', NEW.id, NEW.user_id);

    -- Update the notification count for the user
    UPDATE users SET notification_count = notification_count + 1 WHERE id = NEW.user_id;
END //
DELIMITER ;

ALTER TABLE `ib_users` ADD `face_or_online` int(1) DEFAULT NULL AFTER `pic_path`;
ALTER TABLE `ib_attendances` ADD `face_or_online` int(1) DEFAULT NULL AFTER `user_id`;

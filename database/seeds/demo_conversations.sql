SET NAMES utf8mb4;

INSERT INTO conversations (tenant_id, contact_id, channel, subject, status, assigned_to, last_message_at, last_message_preview, unread_count, is_starred) VALUES
(1, 3, 'email', 'Hỏi về gói Professional', 'open', 2, NOW() - INTERVAL 30 MINUTE, 'Cho tôi hỏi giá gói Pro cho 20 users?', 2, 0),
(1, 5, 'zalo', NULL, 'open', 2, NOW() - INTERVAL 120 MINUTE, 'Anh ơi đơn hàng của em tới đâu rồi?', 1, 1),
(1, 8, 'facebook', 'Tư vấn giải pháp CRM', 'pending', 3, NOW() - INTERVAL 300 MINUTE, 'Dạ em cảm ơn, em sẽ cân nhắc thêm', 0, 0),
(1, 1, 'email', 'Báo lỗi không export được', 'resolved', 6, NOW() - INTERVAL 1440 MINUTE, 'Đã fix rồi anh, anh thử lại giúp em', 0, 0),
(1, 4, 'livechat', NULL, 'open', 3, NOW() - INTERVAL 10 MINUTE, 'Tôi muốn gia hạn thêm 6 tháng', 3, 1),
(1, 10, 'sms', NULL, 'closed', 2, NOW() - INTERVAL 4320 MINUTE, 'Cảm ơn bạn đã xác nhận', 0, 0),
(1, 13, 'phone', 'Yêu cầu demo', 'open', 3, NOW() - INTERVAL 240 MINUTE, 'OK hẹn 2h chiều thứ 4 nhé', 0, 0);

SET @c1 = (SELECT id FROM conversations WHERE contact_id = 3 AND channel = 'email' LIMIT 1);
SET @c2 = (SELECT id FROM conversations WHERE contact_id = 5 AND channel = 'zalo' LIMIT 1);
SET @c3 = (SELECT id FROM conversations WHERE contact_id = 8 AND channel = 'facebook' LIMIT 1);
SET @c4 = (SELECT id FROM conversations WHERE contact_id = 1 AND channel = 'email' LIMIT 1);
SET @c5 = (SELECT id FROM conversations WHERE contact_id = 4 AND channel = 'livechat' LIMIT 1);
SET @c6 = (SELECT id FROM conversations WHERE contact_id = 10 AND channel = 'sms' LIMIT 1);
SET @c7 = (SELECT id FROM conversations WHERE contact_id = 13 AND channel = 'phone' LIMIT 1);

INSERT INTO messages (conversation_id, direction, sender_type, sender_id, content, content_type, created_at) VALUES
(@c1, 'inbound', 'contact', 3, 'Xin chào, cho tôi hỏi giá gói Professional cho team 20 người được không ạ?', 'text', NOW() - INTERVAL 120 MINUTE),
(@c1, 'outbound', 'user', 2, 'Chào anh Cường! Gói Professional giá 1.499.000đ/tháng cho tối đa 20 users. Anh có muốn em gửi báo giá chi tiết không ạ?', 'text', NOW() - INTERVAL 90 MINUTE),
(@c1, 'inbound', 'contact', 3, 'Cho tôi hỏi giá gói Pro cho 20 users?', 'text', NOW() - INTERVAL 30 MINUTE),

(@c2, 'inbound', 'contact', 5, 'Chào shop, đơn DH2603003 của mình giờ tới đâu rồi?', 'text', NOW() - INTERVAL 180 MINUTE),
(@c2, 'outbound', 'user', 2, 'Chào anh Em! Đơn DH2603003 đang được xử lý, dự kiến giao tuần sau ạ.', 'text', NOW() - INTERVAL 150 MINUTE),
(@c2, 'inbound', 'contact', 5, 'Anh ơi đơn hàng của em tới đâu rồi?', 'text', NOW() - INTERVAL 120 MINUTE),

(@c3, 'inbound', 'contact', 8, 'Mình muốn tìm hiểu giải pháp CRM cho bệnh viện, có phù hợp không?', 'text', NOW() - INTERVAL 480 MINUTE),
(@c3, 'outbound', 'user', 3, 'Chào chị Hạnh! ToryCRM hoàn toàn phù hợp cho ngành y tế. Em có thể demo trực tiếp cho chị xem ạ.', 'text', NOW() - INTERVAL 420 MINUTE),
(@c3, 'inbound', 'contact', 8, 'Để mình sắp xếp thời gian rồi liên hệ lại nhé', 'text', NOW() - INTERVAL 360 MINUTE),
(@c3, 'outbound', 'user', 3, 'Dạ vâng ạ! Chị có thể liên hệ bất cứ lúc nào.', 'text', NOW() - INTERVAL 330 MINUTE),
(@c3, 'inbound', 'contact', 8, 'Dạ em cảm ơn, em sẽ cân nhắc thêm', 'text', NOW() - INTERVAL 300 MINUTE),

(@c4, 'inbound', 'contact', 1, 'Khi bấm Export CSV ở trang khách hàng thì bị lỗi trắng trang, không tải được file.', 'text', NOW() - INTERVAL 2880 MINUTE),
(@c4, 'outbound', 'user', 6, 'Chào anh An, em đã ghi nhận lỗi. Đang kiểm tra và sẽ phản hồi sớm nhất ạ.', 'text', NOW() - INTERVAL 2760 MINUTE),
(@c4, 'outbound', 'user', 6, 'Đã fix rồi anh, anh thử lại giúp em. Lỗi do file CSV quá lớn, em đã tối ưu lại.', 'text', NOW() - INTERVAL 1440 MINUTE),

(@c5, 'inbound', 'contact', 4, 'Xin chào, tôi muốn gia hạn license thêm 6 tháng', 'text', NOW() - INTERVAL 20 MINUTE),
(@c5, 'outbound', 'user', 3, 'Chào chị Dung! Em kiểm tra hợp đồng và gửi báo giá gia hạn cho chị nhé.', 'text', NOW() - INTERVAL 15 MINUTE),
(@c5, 'inbound', 'contact', 4, 'Tôi muốn gia hạn thêm 6 tháng', 'text', NOW() - INTERVAL 10 MINUTE),

(@c6, 'outbound', 'user', 2, 'Chào chị Linh, xác nhận lịch hẹn demo ngày mai lúc 10h ạ.', 'text', NOW() - INTERVAL 4400 MINUTE),
(@c6, 'inbound', 'contact', 10, 'Cảm ơn bạn đã xác nhận', 'text', NOW() - INTERVAL 4320 MINUTE),

(@c7, 'outbound', 'user', 3, 'Gọi anh Phong hẹn demo module kho tại văn phòng Minh Phát.', 'text', NOW() - INTERVAL 300 MINUTE),
(@c7, 'inbound', 'contact', 13, 'Đồng ý, hẹn 2h chiều thứ 4 tại văn phòng.', 'text', NOW() - INTERVAL 270 MINUTE),
(@c7, 'outbound', 'user', 3, 'OK hẹn 2h chiều thứ 4 nhé. Em sẽ chuẩn bị demo module kho và đơn hàng mua.', 'text', NOW() - INTERVAL 240 MINUTE);

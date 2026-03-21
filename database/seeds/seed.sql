-- ToryCRM Seed Data

-- Default admin user (password: admin123)
INSERT INTO `users` (`name`, `email`, `password`, `role`, `is_active`) VALUES
('Admin', 'admin@torycrm.com', '$2y$10$Ww5sQbe.Nt29/7U2GHWsV.mOufKGLtVcNSORJ7Knp35X31rSLP2o6', 'admin', 1);

-- Contact sources
INSERT INTO `contact_sources` (`name`, `color`, `sort_order`) VALUES
('Website', '#405189', 1),
('Facebook', '#3b5998', 2),
('Zalo', '#0068ff', 3),
('Điện thoại', '#0ab39c', 4),
('Email', '#f06548', 5),
('Giới thiệu', '#f7b84b', 6),
('Sự kiện', '#299cdb', 7),
('Khác', '#878a99', 8);

-- Default tags
INSERT INTO `tags` (`name`, `color`) VALUES
('VIP', '#f06548'),
('Tiềm năng', '#0ab39c'),
('Đối tác', '#405189'),
('Nhà cung cấp', '#f7b84b'),
('Khách hàng mới', '#299cdb');

-- Deal stages (Pipeline)
INSERT INTO `deal_stages` (`name`, `color`, `probability`, `sort_order`) VALUES
('Tiếp cận', '#405189', 10, 1),
('Tìm hiểu nhu cầu', '#299cdb', 25, 2),
('Đề xuất giải pháp', '#f7b84b', 50, 3),
('Báo giá', '#0ab39c', 75, 4),
('Đàm phán', '#f06548', 90, 5),
('Thắng', '#0ab39c', 100, 6),
('Thua', '#f06548', 0, 7);

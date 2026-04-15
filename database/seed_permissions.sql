-- Clear existing permissions for non-admin groups
DELETE gp FROM group_permissions gp JOIN permission_groups pg ON gp.group_id = pg.id WHERE pg.is_system = 0;

-- QUAN LY KINH DOANH (id=9)
INSERT INTO group_permissions (group_id, permission_id) VALUES
(9,1),(9,2),(9,3),(9,4),(9,97),(9,107),
(9,5),(9,6),(9,7),(9,98),
(9,9),(9,10),(9,11),(9,12),(9,99),(9,108),
(9,21),(9,22),(9,23),(9,25),(9,101),
(9,17),(9,102),
(9,42),(9,106),
(9,13),(9,14),(9,15),(9,100),
(9,30),(9,31),(9,32),(9,104),
(9,34),(9,35),(9,36),
(9,26),(9,27),(9,28),(9,103),
(9,38),
(9,48);

-- KINH DOANH (id=2)
INSERT INTO group_permissions (group_id, permission_id) VALUES
(2,1),(2,2),(2,3),
(2,5),(2,6),(2,7),
(2,9),(2,10),(2,11),
(2,21),(2,22),(2,23),
(2,17),
(2,42),
(2,13),(2,14),(2,15),
(2,30),
(2,34),(2,35),
(2,26),(2,27),(2,28),
(2,48);

-- NVKD Online (id=10)
INSERT INTO group_permissions (group_id, permission_id) VALUES
(10,1),(10,2),(10,3),
(10,5),(10,6),(10,7),
(10,9),(10,10),(10,11),
(10,21),(10,22),(10,23),
(10,17),
(10,42),
(10,13),(10,14),(10,15),
(10,26),(10,27),(10,28),
(10,48);

-- Kinh doanh Toky (id=11)
INSERT INTO group_permissions (group_id, permission_id) VALUES
(11,1),(11,2),(11,3),
(11,5),(11,6),(11,7),
(11,9),(11,10),(11,11),
(11,21),(11,22),(11,23),
(11,17),
(11,42),
(11,13),(11,14),(11,15),
(11,26),(11,27),(11,28),
(11,48);

-- NVKD Du an (id=12)
INSERT INTO group_permissions (group_id, permission_id) VALUES
(12,1),(12,2),(12,3),
(12,5),(12,6),(12,7),
(12,9),(12,10),(12,11),
(12,21),(12,22),(12,23),
(12,17),
(12,42),
(12,13),(12,14),(12,15),
(12,26),(12,27),(12,28),
(12,48);

-- Dai ly (id=13)
INSERT INTO group_permissions (group_id, permission_id) VALUES
(13,1),(13,2),(13,3),
(13,5),(13,6),(13,7),
(13,9),(13,10),(13,11),
(13,21),(13,22),(13,23),
(13,17),
(13,42),
(13,13),(13,14),(13,15),
(13,26),(13,27),(13,28),
(13,48);

-- KE TOAN (id=4)
INSERT INTO group_permissions (group_id, permission_id) VALUES
(4,34),(4,35),(4,36),(4,37),(4,105),
(4,21),(4,23),(4,25),(4,101),
(4,42),(4,106),
(4,1),
(4,5),
(4,9),
(4,17),
(4,13),
(4,48);

-- HANH CHINH - NHAN SU (id=5)
INSERT INTO group_permissions (group_id, permission_id) VALUES
(5,38),(5,39),(5,40),(5,41),
(5,42),(5,106),
(5,13),(5,14),(5,15),
(5,1),
(5,5),
(5,48);

-- MARKETING (id=14)
INSERT INTO group_permissions (group_id, permission_id) VALUES
(14,1),(14,2),(14,3),(14,97),
(14,5),(14,6),(14,7),
(14,30),(14,31),(14,32),(14,33),(14,104),
(14,43),(14,44),
(14,42),(14,106),
(14,13),(14,14),(14,15),
(14,9),(14,10),(14,11),
(14,48);

-- MUA HANG (id=3)
INSERT INTO group_permissions (group_id, permission_id) VALUES
(3,17),(3,18),(3,19),(3,20),(3,102),
(3,21),(3,22),(3,23),(3,101),
(3,34),(3,35),
(3,42),
(3,13),(3,14),(3,15),
(3,1),
(3,5),
(3,48);

-- PHONG DAT HANG (id=15)
INSERT INTO group_permissions (group_id, permission_id) VALUES
(15,21),(15,22),(15,23),(15,101),
(15,17),
(15,1),
(15,5),
(15,13),(15,14),(15,15),
(15,42);

-- SAN XUAT (id=8)
INSERT INTO group_permissions (group_id, permission_id) VALUES
(8,17),(8,18),(8,19),
(8,21),
(8,13),(8,14),(8,15),
(8,42);

-- THIET KE (id=6)
INSERT INTO group_permissions (group_id, permission_id) VALUES
(6,13),(6,14),(6,15),
(6,17),
(6,42);

-- DUNG - VIEW HD (id=16)
INSERT INTO group_permissions (group_id, permission_id) VALUES
(16,1),
(16,5),
(16,9),
(16,21),
(16,17),
(16,42);

-- NHAN SU NGHI VIEC (id=7): khong co quyen gi

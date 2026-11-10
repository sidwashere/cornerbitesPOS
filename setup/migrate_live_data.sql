-- Corner Bites POS — LIVE data parity migration (safe, idempotent).
-- Brings stored_items / stored_categories on the production DB to the
-- same state as the local production DB: launch combos removed, categories
-- renamed, the 5 new menu items added, images + website links set.
-- Sales/history untouched (sale rows carry their own inline item data).
SET NAMES utf8mb4;

-- 1. Remove launch combo products and their category (business is on normal ops)
DELETE FROM stored_items WHERE id IN (1,2,3);
DELETE FROM stored_categories WHERE id = 1;

-- 2. Category names + images to match the production menu
UPDATE stored_categories SET name='Chicken &amp; Pilau', image='https://upload.wikimedia.org/wikipedia/commons/8/8e/Roasted_Chicken_Ongata_Rongai.jpg' WHERE id=2;
UPDATE stored_categories SET name='Chips & Masala', image='' WHERE id=3;
UPDATE stored_categories SET name='Uji & Bites', image='' WHERE id=4;
UPDATE stored_categories SET name='Juice &amp; Sodas', image='/uploads/website/coke-350ml-817f7d84.jpg' WHERE id=5;
UPDATE stored_categories SET name='Drinks', image='' WHERE id=6;
UPDATE stored_categories SET name='Breakfast &amp; Snacks', image='' WHERE id=7;
UPDATE stored_categories SET name='Choma &amp; Grills', image='' WHERE id=8;

-- 3. Item parity: prices, data JSON (image, description, website_id), categories
INSERT INTO stored_items (id, supplierid, categoryid, code, name, price, data)
VALUES (4, 2, 2, 'PILAUSWAHILIBEEFPILAU', 'Pilau (Swahili Beef Pilau)', '150.00', '{\"code\":\"PILAUSWAHILIBEEFPILAU\",\"qty\":999,\"name\":\"Pilau (Swahili Beef Pilau)\",\"alt_name\":\"\",\"description\":\"Authentic aromatic pilau rice...\",\"image\":\"https:\\/\\/upload.wikimedia.org\\/wikipedia\\/commons\\/c\\/c2\\/Swahili_Pilau.jpg\",\"taxid\":1,\"cost\":\"120.00\",\"price\":\"150.00\",\"supplierid\":0,\"categoryid\":2,\"type\":\"food\",\"modifiers\":[],\"website_id\":\"pilau\"}')
ON DUPLICATE KEY UPDATE supplierid=VALUES(supplierid), categoryid=VALUES(categoryid), code=VALUES(code), name=VALUES(name), price=VALUES(price), data=VALUES(data);
INSERT INTO stored_items (id, supplierid, categoryid, code, name, price, data)
VALUES (5, 1, 2, 'CHICKEN14QUARTERCHICKEN', 'Chicken 1/4 (Quarter Chicken)', '190.00', '{\"code\":\"CHICKEN14QUARTERCHICKEN\",\"qty\":999,\"name\":\"Chicken 1\\/4 (Quarter Chicken)\",\"alt_name\":\"\",\"description\":\"Crispy seasoned quarter chicken, fried golden brown with crunchy skin and juicy, flavorful meat inside. Pairs great with Fries or Pilau.\",\"image\":\"https:\\/\\/upload.wikimedia.org\\/wikipedia\\/commons\\/8\\/8e\\/Roasted_Chicken_Ongata_Rongai.jpg\",\"taxid\":1,\"cost\":\"0.00\",\"price\":\"190.00\",\"supplierid\":0,\"categoryid\":2,\"type\":\"food\",\"modifiers\":[],\"website_id\":\"chicken\"}')
ON DUPLICATE KEY UPDATE supplierid=VALUES(supplierid), categoryid=VALUES(categoryid), code=VALUES(code), name=VALUES(name), price=VALUES(price), data=VALUES(data);
INSERT INTO stored_items (id, supplierid, categoryid, code, name, price, data)
VALUES (6, 1, 3, 'FRIESMASALA', 'Fries Masala', '160.00', '{\"code\":\"FRIESMASALA\",\"qty\":999,\"name\":\"Fries Masala\",\"alt_name\":\"\",\"description\":\"Golden hand-cut potato chips smothered in a sizzling hot tomato, onion, and chili masala gravy with lemon and chopped cilantro.\",\"image\":\"https:\\/\\/upload.wikimedia.org\\/wikipedia\\/commons\\/c\\/cd\\/Chicken_masala_%26_home_made_fries.JPG\",\"taxid\":1,\"cost\":\"0.00\",\"price\":\"160.00\",\"supplierid\":0,\"categoryid\":3,\"type\":\"food\",\"modifiers\":[],\"website_id\":\"fries-masala\"}')
ON DUPLICATE KEY UPDATE supplierid=VALUES(supplierid), categoryid=VALUES(categoryid), code=VALUES(code), name=VALUES(name), price=VALUES(price), data=VALUES(data);
INSERT INTO stored_items (id, supplierid, categoryid, code, name, price, data)
VALUES (7, 2, 3, 'FRIESCHIPS', 'Fries (Chips)', '100.00', '{\"code\":\"FRIESCHIPS\",\"qty\":999,\"name\":\"Fries (Chips)\",\"alt_name\":\"\",\"description\":\"Crisp, golden-fried Kenyan potato chips lightly salted and served hot in generous portions with tomato ketchup and chili sauce.\",\"image\":\"https:\\/\\/upload.wikimedia.org\\/wikipedia\\/commons\\/f\\/f9\\/Fries_with_Dry-Fried_pork_%26_Homemade_Sauce.jpg\",\"taxid\":1,\"cost\":\"0.00\",\"price\":\"100.00\",\"supplierid\":0,\"categoryid\":3,\"type\":\"food\",\"modifiers\":[],\"website_id\":\"fries\"}')
ON DUPLICATE KEY UPDATE supplierid=VALUES(supplierid), categoryid=VALUES(categoryid), code=VALUES(code), name=VALUES(name), price=VALUES(price), data=VALUES(data);
INSERT INTO stored_items (id, supplierid, categoryid, code, name, price, data)
VALUES (8, 2, 4, 'SAMOSA', 'Samosa', '50.00', '{\"code\":\"SAMOSA\",\"qty\":999,\"name\":\"Samosa\",\"alt_name\":\"\",\"description\":\"Crispy, flaky triangular pastry pocket stuffed with well-seasoned minced meat, onions, cumin, and chopped coriander.\",\"image\":\"https:\\/\\/upload.wikimedia.org\\/wikipedia\\/commons\\/1\\/18\\/Chicken_samosas_and_lemon_slices.jpg\",\"taxid\":1,\"cost\":\"85.00\",\"price\":\"50.00\",\"supplierid\":0,\"categoryid\":4,\"type\":\"food\",\"modifiers\":[],\"lowlevel\":25,\"website_id\":\"samosa\"}')
ON DUPLICATE KEY UPDATE supplierid=VALUES(supplierid), categoryid=VALUES(categoryid), code=VALUES(code), name=VALUES(name), price=VALUES(price), data=VALUES(data);
INSERT INTO stored_items (id, supplierid, categoryid, code, name, price, data)
VALUES (9, 2, 4, 'UJIPOWER', 'Uji Power', '70.00', '{\"code\":\"UJIPOWER\",\"qty\":999,\"name\":\"Uji Power\",\"alt_name\":\"\",\"description\":\"Nairobi\'s legendary thick, nutritious multigrain porridge infused with ground pumpkin seeds, nuts, and natural honey sweetness.\",\"image\":\"https:\\/\\/upload.wikimedia.org\\/wikipedia\\/commons\\/9\\/94\\/Drinking_traditional_porridge.jpg\",\"taxid\":1,\"cost\":\"0.00\",\"price\":\"70.00\",\"supplierid\":0,\"categoryid\":4,\"type\":\"food\",\"modifiers\":[],\"website_id\":\"uji\"}')
ON DUPLICATE KEY UPDATE supplierid=VALUES(supplierid), categoryid=VALUES(categoryid), code=VALUES(code), name=VALUES(name), price=VALUES(price), data=VALUES(data);
INSERT INTO stored_items (id, supplierid, categoryid, code, name, price, data)
VALUES (10, 1, 4, 'MANDAZIANDAZI', 'Mandazi (Andazi)', '20.00', '{\"code\":\"MANDAZIANDAZI\",\"qty\":999,\"name\":\"Mandazi (Andazi)\",\"alt_name\":\"\",\"description\":\"Soft, fluffy, golden-brown East African sweet fried bread pastry infused with aromatic cardamom. Perfect companion for chai or uji.\",\"image\":\"https:\\/\\/upload.wikimedia.org\\/wikipedia\\/commons\\/5\\/57\\/Mandazis.jpg\",\"taxid\":1,\"cost\":\"0.00\",\"price\":\"20.00\",\"supplierid\":0,\"categoryid\":4,\"type\":\"food\",\"modifiers\":[],\"website_id\":\"mandazi\"}')
ON DUPLICATE KEY UPDATE supplierid=VALUES(supplierid), categoryid=VALUES(categoryid), code=VALUES(code), name=VALUES(name), price=VALUES(price), data=VALUES(data);
INSERT INTO stored_items (id, supplierid, categoryid, code, name, price, data)
VALUES (11, 1, 5, 'FRUITJUICE', 'Fruit Juice', '100.00', '{\"code\":\"FRUITJUICE\",\"qty\":999,\"name\":\"Fruit Juice\",\"alt_name\":\"\",\"description\":\"Freshly blended chilled tropical fruit juice: Mango, Passion, Pineapple or Orange without artificial sweeteners or preservatives.\",\"image\":\"https:\\/\\/upload.wikimedia.org\\/wikipedia\\/commons\\/3\\/39\\/Sugarcane_Juice.jpg\",\"taxid\":1,\"cost\":\"0.00\",\"price\":\"100.00\",\"supplierid\":0,\"categoryid\":5,\"type\":\"beverage\",\"modifiers\":[],\"website_id\":\"juice\"}')
ON DUPLICATE KEY UPDATE supplierid=VALUES(supplierid), categoryid=VALUES(categoryid), code=VALUES(code), name=VALUES(name), price=VALUES(price), data=VALUES(data);
INSERT INTO stored_items (id, supplierid, categoryid, code, name, price, data)
VALUES (12, 0, 5, 'FRUITSALAD', 'Fruit Salad', '50.00', '{\"code\":\"FRUITSALAD\",\"qty\":999,\"name\":\"Fruit Salad\",\"alt_name\":\"\",\"description\":\"Crisp medley of freshly diced watermelon, sweet pineapple, pawpaw, and crisp apple cubes tossed in natural fruit nectar.\",\"image\":\"https:\\/\\/upload.wikimedia.org\\/wikipedia\\/commons\\/1\\/1f\\/Fruit_salad_plate.jpg\",\"taxid\":1,\"cost\":\"0.00\",\"price\":\"50.00\",\"supplierid\":0,\"categoryid\":5,\"type\":\"beverage\",\"modifiers\":[],\"website_id\":\"fruit-salad\"}')
ON DUPLICATE KEY UPDATE supplierid=VALUES(supplierid), categoryid=VALUES(categoryid), code=VALUES(code), name=VALUES(name), price=VALUES(price), data=VALUES(data);
INSERT INTO stored_items (id, supplierid, categoryid, code, name, price, data)
VALUES (13, 0, 5, 'SODA350ML', 'Soda 350ml', '60.00', '{\"code\":\"SODA350ML\",\"qty\":999,\"name\":\"Soda 350ml\",\"alt_name\":\"\",\"description\":\"Crisp ice-cold 350ml soda bottle. Choose Coca-Cola, Fanta Orange, Fanta Blackcurrant, Sprite, or Stoney Tangawizi.\",\"image\":\"\\/uploads\\/website\\/coke-350ml-c396d272.jpg\",\"taxid\":1,\"cost\":\"0.00\",\"price\":\"60.00\",\"supplierid\":0,\"categoryid\":5,\"type\":\"beverage\",\"modifiers\":[],\"website_id\":\"soda350\"}')
ON DUPLICATE KEY UPDATE supplierid=VALUES(supplierid), categoryid=VALUES(categoryid), code=VALUES(code), name=VALUES(name), price=VALUES(price), data=VALUES(data);
INSERT INTO stored_items (id, supplierid, categoryid, code, name, price, data)
VALUES (14, 0, 5, 'SODA500ML', 'Soda 500ml', '80.00', '{\"code\":\"SODA500ML\",\"qty\":999,\"name\":\"Soda 500ml\",\"alt_name\":\"\",\"description\":\"Refreshing 500ml PET bottle served super chilled. Coca-Cola, Fanta, Sprite, Krest Bitter Lemon, or Stoney Tangawizi.\",\"image\":\"https:\\/\\/upload.wikimedia.org\\/wikipedia\\/commons\\/6\\/69\\/Coca_cola_bottle_-_300ml_plastic_-_front_-_01.jpg\",\"taxid\":1,\"cost\":\"0.00\",\"price\":\"80.00\",\"supplierid\":0,\"categoryid\":5,\"type\":\"beverage\",\"modifiers\":[],\"website_id\":\"soda500\"}')
ON DUPLICATE KEY UPDATE supplierid=VALUES(supplierid), categoryid=VALUES(categoryid), code=VALUES(code), name=VALUES(name), price=VALUES(price), data=VALUES(data);
INSERT INTO stored_items (id, supplierid, categoryid, code, name, price, data)
VALUES (16, 0, 7, 'CB82865F2D', 'Sausages', '50.00', '{\"code\":\"CB82865F2D\",\"qty\":100,\"name\":\"Sausages\",\"description\":\"Crispy fried sausages, served steaming hot with fresh pilipili.\",\"taxid\":1,\"cost\":50,\"price\":50,\"type\":\"\",\"supplierid\":0,\"categoryid\":7,\"image\":\"\",\"website_id\":\"sausages\"}')
ON DUPLICATE KEY UPDATE supplierid=VALUES(supplierid), categoryid=VALUES(categoryid), code=VALUES(code), name=VALUES(name), price=VALUES(price), data=VALUES(data);
INSERT INTO stored_items (id, supplierid, categoryid, code, name, price, data)
VALUES (17, 0, 7, 'CB90B6B955', 'Smokies', '40.00', '{\"code\":\"CB90B6B955\",\"qty\":100,\"name\":\"Smokies\",\"description\":\"Classic flavored smokie, deep fried golden and served crisp.\",\"taxid\":1,\"cost\":40,\"price\":40,\"type\":\"\",\"supplierid\":0,\"categoryid\":7,\"image\":\"\",\"website_id\":\"smokies\"}')
ON DUPLICATE KEY UPDATE supplierid=VALUES(supplierid), categoryid=VALUES(categoryid), code=VALUES(code), name=VALUES(name), price=VALUES(price), data=VALUES(data);
INSERT INTO stored_items (id, supplierid, categoryid, code, name, price, data)
VALUES (18, 0, 8, 'CB1F462114', 'Choma Sausages', '150.00', '{\"code\":\"CB1F462114\",\"qty\":100,\"name\":\"Choma Sausages\",\"description\":\"Smoky charcoal-grilled sausages with our special choma glaze.\",\"taxid\":1,\"cost\":150,\"price\":150,\"type\":\"\",\"supplierid\":0,\"categoryid\":8,\"website_id\":\"choma-sausages\"}')
ON DUPLICATE KEY UPDATE supplierid=VALUES(supplierid), categoryid=VALUES(categoryid), code=VALUES(code), name=VALUES(name), price=VALUES(price), data=VALUES(data);
INSERT INTO stored_items (id, supplierid, categoryid, code, name, price, data)
VALUES (19, 0, 7, 'CB34483EB3', 'Chapatis', '30.00', '{\"code\":\"CB34483EB3\",\"qty\":100,\"name\":\"Chapatis\",\"description\":\"Soft, flaky, hand-made chapatis \\u2014 fresh off the pan.\",\"taxid\":1,\"cost\":30,\"price\":30,\"type\":\"\",\"supplierid\":0,\"categoryid\":7,\"image\":\"\",\"website_id\":\"chapatis\"}')
ON DUPLICATE KEY UPDATE supplierid=VALUES(supplierid), categoryid=VALUES(categoryid), code=VALUES(code), name=VALUES(name), price=VALUES(price), data=VALUES(data);
INSERT INTO stored_items (id, supplierid, categoryid, code, name, price, data)
VALUES (20, 0, 6, 'CB7516D98E', 'Tea', '50.00', '{\"code\":\"CB7516D98E\",\"qty\":100,\"name\":\"Tea\",\"description\":\"Hot spiced Kenyan chai brewed fresh with milk and spices.\",\"taxid\":1,\"cost\":50,\"price\":50,\"type\":\"\",\"supplierid\":0,\"categoryid\":6,\"image\":\"\",\"website_id\":\"tea\"}')
ON DUPLICATE KEY UPDATE supplierid=VALUES(supplierid), categoryid=VALUES(categoryid), code=VALUES(code), name=VALUES(name), price=VALUES(price), data=VALUES(data);

SELECT id, name, price FROM stored_items ORDER BY id;

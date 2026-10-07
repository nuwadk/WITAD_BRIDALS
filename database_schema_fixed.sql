-- ============================================================
-- WITAD BRIDAL - COMPLETE DATABASE SCHEMA (FIXED)
-- Run this entire file in phpMyAdmin
-- ============================================================

SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS payments;
DROP TABLE IF EXISTS wishlist;
DROP TABLE IF EXISTS cart_items;
DROP TABLE IF EXISTS order_items;
DROP TABLE IF EXISTS orders;
DROP TABLE IF EXISTS bookings;
DROP TABLE IF EXISTS newsletter_subscribers;
DROP TABLE IF EXISTS product_images;
DROP TABLE IF EXISTS products;
DROP TABLE IF EXISTS categories;
DROP TABLE IF EXISTS blog_posts;
DROP TABLE IF EXISTS testimonials;
DROP TABLE IF EXISTS team_members;
DROP TABLE IF EXISTS gallery_images;
DROP TABLE IF EXISTS settings;
DROP TABLE IF EXISTS customers;
DROP TABLE IF EXISTS feedback;
DROP TABLE IF EXISTS appointments;
DROP TABLE IF EXISTS admins;

SET FOREIGN_KEY_CHECKS = 1;

-- 1. ADMINS
CREATE TABLE `admins` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `role` varchar(20) DEFAULT 'admin',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `admins` (`username`, `password`, `full_name`, `email`, `role`) 
VALUES ('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrator', 'admin@witadbridal.com', 'admin');

-- 2. CUSTOMERS
CREATE TABLE `customers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `password` varchar(255) NOT NULL,
  `wedding_date` date DEFAULT NULL,
  `address` text DEFAULT NULL,
  `avatar` varchar(255) DEFAULT NULL,
  `status` varchar(20) DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. CATEGORIES
CREATE TABLE `categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `slug` varchar(190) NOT NULL,
  `description` text DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `sort_order` int(11) DEFAULT 0,
  `status` varchar(20) DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `categories` (`name`, `slug`, `description`, `sort_order`) VALUES
('Wedding Gowns', 'wedding-gowns', 'Stunning wedding gowns for every bride', 1),
('Bridesmaids Dresses', 'bridesmaids', 'Beautiful coordinated bridesmaid dresses', 2),
('Bridal Accessories', 'accessories', 'Veils, tiaras, jewelry and more', 3),
('Evening Gowns', 'evening-gowns', 'Elegant evening wear for special occasions', 4),
('Traditional Wear', 'traditional', 'Cultural and traditional bridal attire', 5);

-- 4. PRODUCTS
CREATE TABLE `products` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `category_id` int(11) NOT NULL,
  `name` varchar(200) NOT NULL,
  `slug` varchar(190) NOT NULL,
  `description` text DEFAULT NULL,
  `short_desc` varchar(500) DEFAULT NULL,
  `price` decimal(12,2) NOT NULL,
  `sale_price` decimal(12,2) DEFAULT NULL,
  `rental_price` decimal(12,2) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `sizes` varchar(255) DEFAULT NULL,
  `colors` varchar(255) DEFAULT NULL,
  `material` varchar(100) DEFAULT NULL,
  `style` varchar(100) DEFAULT NULL,
  `featured` tinyint(1) DEFAULT 0,
  `status` varchar(20) DEFAULT 'active',
  `views` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  KEY `category_id` (`category_id`),
  KEY `featured` (`featured`),
  KEY `status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `products` (`category_id`, `name`, `slug`, `description`, `short_desc`, `price`, `sale_price`, `rental_price`, `image`, `sizes`, `colors`, `material`, `style`, `featured`) VALUES
(1, 'Ivory A-Line Lace Gown', 'ivory-a-line-lace-gown', 'A stunning ivory A-line gown featuring delicate lace appliques, a sweetheart neckline, and a flowing skirt that creates an ethereal silhouette. Perfect for the romantic bride.', 'Elegant lace A-line gown with sweetheart neckline', 2500000.00, 2200000.00, 800000.00, 'https://images.unsplash.com/photo-1519741497674-611481863552?w=600&q=80', 'XS,S,M,L,XL,2XL', 'Ivory,Champagne,White', 'Lace, Tulle', 'A-Line', 1),
(1, 'Mermaid Pearl Embellished Gown', 'mermaid-pearl-embellished-gown', 'A show-stopping mermaid gown adorned with hand-sewn pearls and crystals. The fitted bodice hugs your curves before flaring into a dramatic train.', 'Pearl embellished mermaid gown with dramatic train', 3500000.00, NULL, 1200000.00, 'https://images.unsplash.com/photo-1594552072238-b8a33785b6cd?w=600&q=80', 'S,M,L,XL', 'White,Ivory', 'Satin, Pearl', 'Mermaid', 1),
(1, 'Bohemian Off-Shoulder Gown', 'bohemian-off-shoulder-gown', 'A dreamy bohemian gown with off-shoulder sleeves and a flowing chiffon skirt. Perfect for garden and outdoor weddings.', 'Romantic off-shoulder bohemian style gown', 1800000.00, 1500000.00, 600000.00, 'https://images.unsplash.com/photo-1583939003579-730e3918a45a?w=600&q=80', 'XS,S,M,L', 'White,Ivory,Blush', 'Chiffon, Lace', 'Bohemian', 1),
(1, 'Classic Ballgown with Crystal Belt', 'classic-ballgown-crystal-belt', 'A timeless ballgown with a voluminous tulle skirt and a sparkling crystal belt. The epitome of princess elegance.', 'Timeless ballgown with crystal belt detail', 2800000.00, NULL, 1000000.00, 'https://images.unsplash.com/photo-1568295093565-cbe7df069e3f?w=600&q=80', 'S,M,L,XL,2XL', 'White,Ivory', 'Tulle, Satin', 'Ballgown', 1),
(1, 'Sleek Satin Sheath Gown', 'sleek-satin-sheath-gown', 'A minimalist satin sheath gown for the modern bride. Clean lines, elegant drape, and timeless sophistication.', 'Minimalist satin sheath gown for modern brides', 2000000.00, 1750000.00, 700000.00, 'https://images.unsplash.com/photo-1521543298264-785fba5614bd?w=600&q=80', 'XS,S,M,L', 'White,Ivory,Champagne', 'Satin', 'Sheath', 0),
(2, 'Blush Pink Bridesmaid Dress', 'blush-pink-bridesmaid-dress', 'Elegant blush pink bridesmaid dress with a flattering A-line cut and delicate chiffon overlay.', 'Blush pink chiffon bridesmaid dress', 350000.00, 300000.00, 150000.00, 'https://images.unsplash.com/photo-1511285560929-80b456fea0bc?w=600&q=80', 'XS,S,M,L,XL', 'Blush,Navy,Sage', 'Chiffon', 'A-Line', 0),
(2, 'Navy Blue Maxi Dress', 'navy-blue-maxi-dress', 'Sophisticated navy blue maxi dress with a V-neckline and flowing skirt. A versatile choice for any bridal party.', 'Navy blue maxi bridesmaid dress', 400000.00, NULL, 180000.00, 'https://images.unsplash.com/photo-1580489944761-15a19d654956?w=600&q=80', 'S,M,L,XL', 'Navy,Burgundy,Emerald', 'Polyester', 'Maxi', 0),
(3, 'Crystal Bridal Tiara', 'crystal-bridal-tiara', 'A dazzling crystal tiara that adds royal elegance to any bridal look. Handcrafted with Swarovski crystals.', 'Swarovski crystal bridal tiara', 250000.00, 200000.00, NULL, 'https://images.unsplash.com/photo-1465495976277-4387d4b0b4c6?w=600&q=80', 'One Size', 'Silver,Gold,Rose Gold', 'Crystal', 'Accessory', 1),
(3, 'Cathedral Length Veil', 'cathedral-length-veil', 'A breathtaking cathedral length veil with delicate lace trim. The perfect finishing touch for your bridal ensemble.', 'Cathedral veil with lace trim', 350000.00, NULL, 100000.00, 'https://images.unsplash.com/photo-1529626455594-4ff0802cfb7e?w=600&q=80', 'One Size', 'White,Ivory', 'Tulle, Lace', 'Veil', 0),
(4, 'Gold Evening Gown', 'gold-evening-gown', 'A glamorous gold evening gown with a plunging neckline and thigh-high slit. Perfect for receptions and evening events.', 'Glamorous gold evening gown', 1500000.00, 1200000.00, 500000.00, 'https://images.unsplash.com/photo-1531746020798-e6953c6e8e04?w=600&q=80', 'S,M,L', 'Gold,Black', 'Sequin, Satin', 'Evening', 0);

-- 5. PRODUCT IMAGES
CREATE TABLE `product_images` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL,
  `image` varchar(255) NOT NULL,
  `sort_order` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 6. WISHLIST
CREATE TABLE `wishlist` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `customer_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `customer_product` (`customer_id`, `product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 7. ORDERS
CREATE TABLE `orders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `customer_id` int(11) NOT NULL,
  `order_number` varchar(50) NOT NULL,
  `total` decimal(12,2) NOT NULL,
  `status` varchar(20) DEFAULT 'pending',
  `payment_method` varchar(50) DEFAULT NULL,
  `payment_status` varchar(20) DEFAULT 'pending',
  `shipping_address` text DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `order_number` (`order_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 8. ORDER ITEMS
CREATE TABLE `order_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `product_name` varchar(200) NOT NULL,
  `price` decimal(12,2) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `size` varchar(20) DEFAULT NULL,
  `color` varchar(50) DEFAULT NULL,
  `type` varchar(20) DEFAULT 'purchase',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 9. BOOKINGS
CREATE TABLE `bookings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `customer_id` int(11) DEFAULT NULL,
  `full_name` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `wedding_date` date DEFAULT NULL,
  `service_needed` varchar(100) DEFAULT 'General Inquiry',
  `notes` text DEFAULT NULL,
  `booking_date` date NOT NULL,
  `booking_time` time NOT NULL,
  `status` varchar(20) DEFAULT 'pending',
  `reminder_sent` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 10. FEEDBACK
CREATE TABLE `feedback` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `customer_id` int(11) DEFAULT NULL,
  `full_name` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `wedding_date` date DEFAULT NULL,
  `service_needed` varchar(100) DEFAULT 'General Inquiry',
  `notes` text DEFAULT NULL,
  `submitted_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `status` varchar(20) DEFAULT 'pending',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 11. TESTIMONIALS
CREATE TABLE `testimonials` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `customer_name` varchar(100) NOT NULL,
  `location` varchar(100) DEFAULT NULL,
  `wedding_date` date DEFAULT NULL,
  `rating` int(11) DEFAULT 5,
  `content` text NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `status` varchar(20) DEFAULT 'active',
  `sort_order` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `testimonials` (`customer_name`, `location`, `wedding_date`, `rating`, `content`, `image`, `sort_order`) VALUES
('Sarah Nakamura', 'Kabwohe-Sheema', '2025-03-15', 5, 'Witad Bridal Collection made my wedding preparations effortless. The team was professional, welcoming, and helped me find the perfect gown. I felt like a true queen!', 'https://images.unsplash.com/photo-1531746020798-e6953c6e8e04?w=100&q=80', 1),
('Amara Kiggundu', 'Entebbe', '2025-01-18', 5, 'From the moment I walked in, I felt at home. The collection is breathtaking and the staff truly understands what every bride needs. Highly recommend Witad!', 'https://images.unsplash.com/photo-1529626455594-4ff0802cfb7e?w=100&q=80', 2),
('Grace Otim', 'Jinja', '2024-12-07', 5, 'The alterations team was phenomenal. My dress fit like it was made for me. I could not have asked for a more beautiful experience. Thank you Witad!', 'https://images.unsplash.com/photo-1580489944761-15a19d654956?w=100&q=80', 3),
('Diana Nakirya', 'Kampala', '2024-10-22', 5, 'I was nervous about finding the right gown, but the Witad team made it so joyful. They listened to every detail and guided me perfectly. I cried when I found my dress!', 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=100&q=80', 4),
('Patricia Nalwoga', 'Mbarara', '2024-08-05', 5, 'Beautiful selection, warm staff, and a lovely atmosphere. My bridesmaids dresses were absolutely gorgeous. Everyone kept complimenting us! Witad is truly special.', 'https://images.unsplash.com/photo-1544717301-9cdcb1f5940f?w=100&q=80', 5),
('Sandra Akello', 'Gulu', '2024-06-14', 5, 'The rental option was perfect for my budget. I looked like a million shillings and everyone thought my gown was custom-made. Thank you Witad for making my dream accessible!', 'https://images.unsplash.com/photo-1521543298264-785fba5614bd?w=100&q=80', 6);

-- 12. BLOG POSTS
CREATE TABLE `blog_posts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `slug` varchar(190) NOT NULL,
  `excerpt` text DEFAULT NULL,
  `content` longtext DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `category` varchar(100) DEFAULT 'General',
  `author` varchar(100) DEFAULT 'Witad Team',
  `views` int(11) DEFAULT 0,
  `featured` tinyint(1) DEFAULT 0,
  `status` varchar(20) DEFAULT 'published',
  `published_at` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `blog_posts` (`title`, `slug`, `excerpt`, `content`, `image`, `category`, `featured`, `published_at`) VALUES
('2025 Wedding Gown Silhouettes', '2025-wedding-gown-silhouettes', 'This season bridal collections are celebrating body diversity and personal expression more than ever.', '<p>This season bridal collections are celebrating body diversity and personal expression more than ever. From flowy bohemian silhouettes to sculpted mermaid gowns, discover which style speaks to your soul.</p><h3>A-Line: The Universal Flatterer</h3><p>The A-line silhouette is beloved for its ability to flatter every body type...</p>', 'https://images.unsplash.com/photo-1519741497674-611481863552?w=800&q=80', 'Bridal Fashion Trends', 1, '2025-05-20'),
('12 Things Every Bride Should Do 3 Months Before the Wedding', 'bride-checklist-3-months', 'From confirming your final fitting to choosing the right accessories, here is your complete pre-wedding checklist.', '<p>The final three months before your wedding are crucial. Here is everything you need to tick off your list...</p>', 'https://images.unsplash.com/photo-1465495976277-4387d4b0b4c6?w=400&q=80', 'Wedding Planning Tips', 0, '2025-05-12'),
('Skincare Secrets for a Glowing Wedding Day Complexion', 'skincare-wedding-day', 'Start this 8-week skincare ritual and walk down the aisle with the most luminous skin of your life.', '<p>Your wedding day skincare starts months in advance. Here is the ultimate routine...</p>', 'https://images.unsplash.com/photo-1594552072238-b8a33785b6cd?w=400&q=80', 'Bridal Beauty Advice', 0, '2025-05-05'),
('How to Choose the Perfect Bridal Veil for Your Gown Style', 'choose-perfect-bridal-veil', 'Cathedral, fingertip, or blusher? Our guide matches every veil style to every gown silhouette.', '<p>The veil is the ultimate bridal accessory. Let us help you find the perfect match...</p>', 'https://images.unsplash.com/photo-1583939003579-730e3918a45a?w=400&q=80', 'Accessory Guides', 0, '2025-04-28'),
('Sarah and David: A Garden Wedding in Kabwohe-Sheema', 'sarah-david-garden-wedding', 'Read Sarah beautiful love story and discover the gown she chose for her magical garden ceremony.', '<p>Sarah and David love story began in university. Their garden wedding was nothing short of magical...</p>', 'https://images.unsplash.com/photo-1511285560929-80b456fea0bc?w=400&q=80', 'Real Bride Stories', 0, '2025-04-18');

-- 13. TEAM MEMBERS
CREATE TABLE `team_members` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `role` varchar(100) NOT NULL,
  `bio` text DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `sort_order` int(11) DEFAULT 0,
  `status` varchar(20) DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `team_members` (`name`, `role`, `bio`, `image`, `sort_order`) VALUES
('Witad Atukunda', 'Founder & Lead Stylist', 'With over 10 years of experience in bridal fashion, Witad founded the collection with a vision to make every bride feel extraordinary.', 'atukunda.jpeg', 1),
('Abigaba Babra', 'Bridal Consultant', 'Babra brings warmth and expertise to every consultation, helping brides discover their perfect style.', 'Abigaba.jpeg', 2),
('Akankwatsa Patricia', 'Head of Alterations', 'Patricia ensures every gown fits like a dream with her meticulous attention to detail.', '22.jpg', 3),
('Atukwase Blessing', 'Accessories Specialist', 'Blessing curates the finest accessories to complete every bridal look with elegance.', '2.jpg', 4);

-- 14. GALLERY IMAGES
CREATE TABLE `gallery_images` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(200) DEFAULT NULL,
  `image` varchar(255) NOT NULL,
  `category` varchar(50) DEFAULT 'general',
  `sort_order` int(11) DEFAULT 0,
  `status` varchar(20) DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `gallery_images` (`title`, `image`, `category`, `sort_order`) VALUES
('Ivory A-Line Gown', 'https://images.unsplash.com/photo-1519741497674-611481863552?w=600&q=80', 'gowns', 1),
('Happy Bride', 'https://images.unsplash.com/photo-1531746020798-e6953c6e8e04?w=600&q=80', 'brides', 2),
('Bridal Accessories', 'https://images.unsplash.com/photo-1465495976277-4387d4b0b4c6?w=600&q=80', 'accessories', 3),
('Lace Ballgown', 'https://images.unsplash.com/photo-1594552072238-b8a33785b6cd?w=600&q=80', 'gowns', 4),
('Bridesmaids in Blush', 'https://images.unsplash.com/photo-1511285560929-80b456fea0bc?w=600&q=80', 'bridesmaids', 5),
('Spring Collection', 'https://images.unsplash.com/photo-1568295093565-cbe7df069e3f?w=600&q=80', 'collections', 6),
('Elegant Mermaid Gown', 'https://images.unsplash.com/photo-1583939003579-730e3918a45a?w=600&q=80', 'gowns', 7),
('Radiant Bride', 'https://images.unsplash.com/photo-1544717301-9cdcb1f5940f?w=600&q=80', 'brides', 8),
('Couture Collection', 'https://images.unsplash.com/photo-1521543298264-785fba5614bd?w=600&q=80', 'collections', 9),
('Jewelry and Details', 'https://images.unsplash.com/photo-1529626455594-4ff0802cfb7e?w=600&q=80', 'accessories', 10),
('Elegant Bridesmaids', 'https://images.unsplash.com/photo-1580489944761-15a19d654956?w=600&q=80', 'bridesmaids', 11),
('Wedding Day Joy', 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=600&q=80', 'brides', 12);

-- 15. SETTINGS
CREATE TABLE `settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `setting_key` (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `settings` (`setting_key`, `setting_value`) VALUES
('site_name', 'Witad Bridal Collection'),
('site_tagline', 'Where Dreams Meet Elegance'),
('site_description', 'Uganda premier bridal destination offering wedding gowns, bridesmaids dresses, accessories, and personalized styling services.'),
('contact_phone', '+256 750 900 134'),
('contact_email', 'info@witadbridal.com'),
('contact_address', 'Witad Bridal Collection, Kabwohe-Sheema, Uganda'),
('business_hours', 'Mon-Fri: 8AM-7PM, Sat: 9AM-6PM, Sun: By Appointment'),
('whatsapp_number', '256750900134'),
('facebook_url', ''),
('instagram_url', ''),
('tiktok_url', ''),
('google_maps_url', 'https://maps.app.goo.gl/xhnR28g73qb2jXR17'),
('currency', 'UGX'),
('tax_rate', '0'),
('shipping_fee', '0'),
('booking_interval', '60'),
('booking_start_time', '08:00'),
('booking_end_time', '19:00'),
('booking_days_ahead', '30');

-- 16. NEWSLETTER SUBSCRIBERS
CREATE TABLE `newsletter_subscribers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(100) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `status` varchar(20) DEFAULT 'active',
  `subscribed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 17. PAYMENTS
CREATE TABLE `payments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) DEFAULT NULL,
  `booking_id` int(11) DEFAULT NULL,
  `customer_id` int(11) DEFAULT NULL,
  `amount` decimal(12,2) NOT NULL,
  `payment_method` varchar(50) NOT NULL,
  `transaction_id` varchar(255) DEFAULT NULL,
  `phone_number` varchar(20) DEFAULT NULL,
  `status` varchar(20) DEFAULT 'pending',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 18. APPOINTMENTS
CREATE TABLE `appointments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `full_name` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `wedding_date` date DEFAULT NULL,
  `service_needed` varchar(100) DEFAULT 'General Inquiry',
  `notes` text DEFAULT NULL,
  `appointment_date` date DEFAULT NULL,
  `appointment_time` time DEFAULT NULL,
  `status` varchar(20) DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Verify all tables created
SHOW TABLES;

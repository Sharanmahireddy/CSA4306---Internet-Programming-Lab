-- ========================================================
-- INTERNET PROGRAMMING LAB - ALL 20 DATABASES SCHEMA & DATA
-- Compatible with MySQL / MariaDB (XAMPP phpMyAdmin)
-- ========================================================

-- --------------------------------------------------------
-- 1. LIBRARY MANAGEMENT SYSTEM
-- --------------------------------------------------------
CREATE DATABASE IF NOT EXISTS `db_01_library` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `db_01_library`;

CREATE TABLE IF NOT EXISTS `books` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `title` VARCHAR(150) NOT NULL,
    `author` VARCHAR(100) NOT NULL,
    `category` VARCHAR(50) DEFAULT 'General',
    `copies` INT NOT NULL DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `students` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `email` VARCHAR(120) NOT NULL UNIQUE,
    `department` VARCHAR(50) DEFAULT 'Computer Science',
    `registered_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `borrow_records` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `book_id` INT NOT NULL,
    `student_email` VARCHAR(120) NOT NULL,
    `issue_date` DATE NOT NULL,
    `due_date` DATE NOT NULL,
    `fine_amount` DECIMAL(8,2) DEFAULT 0.00,
    `status` ENUM('borrowed', 'returned', 'overdue') DEFAULT 'borrowed',
    FOREIGN KEY (`book_id`) REFERENCES `books`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

INSERT INTO `books` (`id`, `title`, `author`, `category`, `copies`) VALUES
(1, 'Data Structures and Algorithms', 'S. Lipschutz', 'Computer Science', 4),
(2, 'Introduction to Python', 'Guido van Rossum', 'Programming', 3),
(3, 'Database Management Systems', 'Raghu Ramakrishnan', 'Databases', 2),
(4, 'Computer Networks', 'Andrew S. Tanenbaum', 'Networking', 5)
ON DUPLICATE KEY UPDATE `title`=VALUES(`title`);

INSERT INTO `students` (`id`, `name`, `email`, `department`) VALUES
(1, 'Pandi Rajan', 'pandi@example.com', 'Information Technology'),
(2, 'Anita Kumar', 'anita@example.com', 'Computer Science')
ON DUPLICATE KEY UPDATE `name`=VALUES(`name`);

INSERT INTO `borrow_records` (`book_id`, `student_email`, `issue_date`, `due_date`, `fine_amount`, `status`) VALUES
(1, 'pandi@example.com', CURDATE(), DATE_ADD(CURDATE(), INTERVAL 14 DAY), 0.00, 'borrowed')
ON DUPLICATE KEY UPDATE `fine_amount`=VALUES(`fine_amount`);


-- --------------------------------------------------------
-- 2. HOTEL BOOKING SYSTEM
-- --------------------------------------------------------
CREATE DATABASE IF NOT EXISTS `db_02_hotel` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `db_02_hotel`;

CREATE TABLE IF NOT EXISTS `rooms` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `room_number` VARCHAR(10) NOT NULL UNIQUE,
    `room_type` ENUM('Deluxe', 'Super Deluxe', 'Executive Suite', 'Standard') NOT NULL,
    `price_per_night` DECIMAL(10,2) NOT NULL,
    `capacity` INT NOT NULL DEFAULT 2,
    `status` ENUM('available', 'occupied', 'maintenance') DEFAULT 'available'
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `bookings` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `guest_name` VARCHAR(100) NOT NULL,
    `guest_email` VARCHAR(120) NOT NULL,
    `guest_phone` VARCHAR(20) NOT NULL,
    `room_id` INT NOT NULL,
    `check_in` DATE NOT NULL,
    `check_out` DATE NOT NULL,
    `total_amount` DECIMAL(10,2) NOT NULL,
    `booking_status` ENUM('confirmed', 'checked_in', 'cancelled') DEFAULT 'confirmed',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`room_id`) REFERENCES `rooms`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

INSERT INTO `rooms` (`id`, `room_number`, `room_type`, `price_per_night`, `capacity`, `status`) VALUES
(1, '101', 'Deluxe', 2500.00, 2, 'available'),
(2, '102', 'Super Deluxe', 3800.00, 3, 'available'),
(3, '201', 'Executive Suite', 5500.00, 4, 'available')
ON DUPLICATE KEY UPDATE `price_per_night`=VALUES(`price_per_night`);

INSERT INTO `bookings` (`guest_name`, `guest_email`, `guest_phone`, `room_id`, `check_in`, `check_out`, `total_amount`) VALUES
('Rajesh V', 'rajesh@example.com', '9876543210', 1, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 2 DAY), 5000.00);


-- --------------------------------------------------------
-- 3. BUS TICKET BOOKING SYSTEM
-- --------------------------------------------------------
CREATE DATABASE IF NOT EXISTS `db_03_bus` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `db_03_bus`;

CREATE TABLE IF NOT EXISTS `buses` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `bus_number` VARCHAR(20) NOT NULL,
    `travels_name` VARCHAR(100) NOT NULL,
    `from_city` VARCHAR(50) NOT NULL,
    `to_city` VARCHAR(50) NOT NULL,
    `departure_time` TIME NOT NULL,
    `fare` DECIMAL(8,2) NOT NULL,
    `total_seats` INT NOT NULL DEFAULT 40,
    `available_seats` INT NOT NULL DEFAULT 40
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `bookings` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `pnr` VARCHAR(20) NOT NULL UNIQUE,
    `bus_id` INT NOT NULL,
    `passenger_name` VARCHAR(100) NOT NULL,
    `seat_number` INT NOT NULL,
    `travel_date` DATE NOT NULL,
    `contact_phone` VARCHAR(20) NOT NULL,
    `booking_status` ENUM('confirmed', 'cancelled') DEFAULT 'confirmed',
    FOREIGN KEY (`bus_id`) REFERENCES `buses`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

INSERT INTO `buses` (`id`, `bus_number`, `travels_name`, `from_city`, `to_city`, `departure_time`, `fare`, `total_seats`, `available_seats`) VALUES
(1, 'TN-01-AB-1234', 'Express Travels', 'Chennai', 'Madurai', '21:30:00', 650.00, 40, 38),
(2, 'TN-09-CD-5678', 'Greenline Travels', 'Chennai', 'Coimbatore', '22:00:00', 750.00, 36, 36)
ON DUPLICATE KEY UPDATE `fare`=VALUES(`fare`);


-- --------------------------------------------------------
-- 4. TRAIN RESERVATION SYSTEM
-- --------------------------------------------------------
CREATE DATABASE IF NOT EXISTS `db_04_train` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `db_04_train`;

CREATE TABLE IF NOT EXISTS `trains` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `train_number` VARCHAR(10) NOT NULL UNIQUE,
    `train_name` VARCHAR(100) NOT NULL,
    `source_station` VARCHAR(50) NOT NULL,
    `destination_station` VARCHAR(50) NOT NULL,
    `departure_time` TIME NOT NULL,
    `arrival_time` TIME NOT NULL,
    `ac_seats` INT NOT NULL DEFAULT 50,
    `sleeper_seats` INT NOT NULL DEFAULT 120
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `reservations` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `pnr_number` VARCHAR(10) NOT NULL UNIQUE,
    `train_id` INT NOT NULL,
    `passenger_name` VARCHAR(100) NOT NULL,
    `age` INT NOT NULL,
    `gender` ENUM('M', 'F', 'Other') NOT NULL,
    `class_type` ENUM('1A', '2A', '3A', 'SL') NOT NULL,
    `journey_date` DATE NOT NULL,
    `status` ENUM('CNF', 'RAC', 'WL', 'CAN') DEFAULT 'CNF',
    FOREIGN KEY (`train_id`) REFERENCES `trains`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

INSERT INTO `trains` (`id`, `train_number`, `train_name`, `source_station`, `destination_station`, `departure_time`, `arrival_time`) VALUES
(1, '12635', 'Vaigai Superfast Express', 'Chennai Egmore', 'Madurai Jn', '13:50:00', '21:20:00'),
(2, '12671', 'Nilgiri Express', 'Chennai Central', 'Mettupalayam', '21:05:00', '06:15:00')
ON DUPLICATE KEY UPDATE `train_name`=VALUES(`train_name`);


-- --------------------------------------------------------
-- 5. FLIGHT RESERVATION SYSTEM
-- --------------------------------------------------------
CREATE DATABASE IF NOT EXISTS `db_05_flight` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `db_05_flight`;

CREATE TABLE IF NOT EXISTS `flights` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `flight_code` VARCHAR(10) NOT NULL UNIQUE,
    `airline` VARCHAR(50) NOT NULL,
    `origin` VARCHAR(50) NOT NULL,
    `destination` VARCHAR(50) NOT NULL,
    `departure` DATETIME NOT NULL,
    `price` DECIMAL(10,2) NOT NULL,
    `seats_available` INT NOT NULL DEFAULT 180
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `passengers` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `booking_reference` VARCHAR(8) NOT NULL UNIQUE,
    `flight_id` INT NOT NULL,
    `passenger_name` VARCHAR(100) NOT NULL,
    `passport_number` VARCHAR(30) DEFAULT NULL,
    `seat_assignment` VARCHAR(5) NOT NULL,
    `status` ENUM('confirmed', 'cancelled', 'boarded') DEFAULT 'confirmed',
    FOREIGN KEY (`flight_id`) REFERENCES `flights`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

INSERT INTO `flights` (`id`, `flight_code`, `airline`, `origin`, `destination`, `departure`, `price`, `seats_available`) VALUES
(1, '6E-452', 'IndiGo', 'MAA (Chennai)', 'DEL (Delhi)', '2026-11-01 08:30:00', 5200.00, 150),
(2, 'AI-541', 'Air India', 'MAA (Chennai)', 'BOM (Mumbai)', '2026-11-01 14:15:00', 4800.00, 120)
ON DUPLICATE KEY UPDATE `price`=VALUES(`price`);


-- --------------------------------------------------------
-- 6. CAKE ORDERING SYSTEM
-- --------------------------------------------------------
CREATE DATABASE IF NOT EXISTS `db_06_cake` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `db_06_cake`;

CREATE TABLE IF NOT EXISTS `cakes` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `flavor` VARCHAR(50) NOT NULL,
    `weight_kg` DECIMAL(3,1) NOT NULL DEFAULT 1.0,
    `price` DECIMAL(8,2) NOT NULL,
    `in_stock` TINYINT(1) DEFAULT 1
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `orders` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `customer_name` VARCHAR(100) NOT NULL,
    `phone` VARCHAR(20) NOT NULL,
    `delivery_address` TEXT NOT NULL,
    `cake_id` INT NOT NULL,
    `custom_message` VARCHAR(100) DEFAULT NULL,
    `delivery_date` DATE NOT NULL,
    `total_amount` DECIMAL(8,2) NOT NULL,
    `order_status` ENUM('pending', 'baking', 'out_for_delivery', 'delivered') DEFAULT 'pending',
    FOREIGN KEY (`cake_id`) REFERENCES `cakes`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

INSERT INTO `cakes` (`id`, `name`, `flavor`, `weight_kg`, `price`) VALUES
(1, 'Black Forest Classic', 'Chocolate Cherry', 1.0, 650.00),
(2, 'Red Velvet Delight', 'Vanilla Cream Cheese', 1.0, 850.00),
(3, 'Choco Truffle Royal', 'Dark Chocolate', 1.5, 1100.00)
ON DUPLICATE KEY UPDATE `price`=VALUES(`price`);


-- --------------------------------------------------------
-- 7. BEAUTY PARLOUR APPOINTMENT
-- --------------------------------------------------------
CREATE DATABASE IF NOT EXISTS `db_07_beauty_parlour` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `db_07_beauty_parlour`;

CREATE TABLE IF NOT EXISTS `services` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `service_name` VARCHAR(100) NOT NULL,
    `category` ENUM('Hair', 'Skin', 'Makeup', 'Spa', 'Nails') NOT NULL,
    `duration_mins` INT NOT NULL,
    `price` DECIMAL(8,2) NOT NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `appointments` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `client_name` VARCHAR(100) NOT NULL,
    `client_phone` VARCHAR(20) NOT NULL,
    `service_id` INT NOT NULL,
    `appointment_date` DATE NOT NULL,
    `appointment_time` TIME NOT NULL,
    `stylist_preference` VARCHAR(50) DEFAULT 'Any Stylist',
    `status` ENUM('scheduled', 'completed', 'cancelled') DEFAULT 'scheduled',
    FOREIGN KEY (`service_id`) REFERENCES `services`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

INSERT INTO `services` (`id`, `service_name`, `category`, `duration_mins`, `price`) VALUES
(1, 'Bridal Glow Facial', 'Skin', 60, 1500.00),
(2, 'Hair Keratin Treatment', 'Hair', 120, 3200.00),
(3, 'Gel Nail Art & Manicure', 'Nails', 45, 800.00)
ON DUPLICATE KEY UPDATE `price`=VALUES(`price`);


-- --------------------------------------------------------
-- 8. HOSPITAL / CLINIC APPOINTMENT
-- --------------------------------------------------------
CREATE DATABASE IF NOT EXISTS `db_08_hospital` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `db_08_hospital`;

CREATE TABLE IF NOT EXISTS `doctors` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `specialty` VARCHAR(100) NOT NULL,
    `qualification` VARCHAR(50) NOT NULL,
    `consultation_fee` DECIMAL(8,2) NOT NULL,
    `available_time` VARCHAR(100) NOT NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `appointments` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `patient_name` VARCHAR(100) NOT NULL,
    `patient_phone` VARCHAR(20) NOT NULL,
    `patient_age` INT NOT NULL,
    `doctor_id` INT NOT NULL,
    `appointment_date` DATE NOT NULL,
    `symptoms` TEXT DEFAULT NULL,
    `status` ENUM('booked', 'consulted', 'cancelled') DEFAULT 'booked',
    FOREIGN KEY (`doctor_id`) REFERENCES `doctors`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

INSERT INTO `doctors` (`id`, `name`, `specialty`, `qualification`, `consultation_fee`, `available_time`) VALUES
(1, 'Dr. Ramesh Nathan', 'Cardiology', 'MD, DM (Cardio)', 600.00, '09:00 AM - 01:00 PM'),
(2, 'Dr. Priya Sundaram', 'General Pediatrics', 'MBBS, DCH', 400.00, '04:00 PM - 08:00 PM')
ON DUPLICATE KEY UPDATE `consultation_fee`=VALUES(`consultation_fee`);


-- --------------------------------------------------------
-- 9. TECHSAVVY ELECTRONICS STORE
-- --------------------------------------------------------
CREATE DATABASE IF NOT EXISTS `db_09_electronics` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `db_09_electronics`;

CREATE TABLE IF NOT EXISTS `products` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(150) NOT NULL,
    `brand` VARCHAR(50) NOT NULL,
    `category` VARCHAR(50) NOT NULL,
    `price` DECIMAL(10,2) NOT NULL,
    `stock_quantity` INT NOT NULL DEFAULT 0
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `orders` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `order_code` VARCHAR(20) NOT NULL UNIQUE,
    `customer_name` VARCHAR(100) NOT NULL,
    `customer_email` VARCHAR(120) NOT NULL,
    `product_id` INT NOT NULL,
    `quantity` INT NOT NULL DEFAULT 1,
    `total_amount` DECIMAL(10,2) NOT NULL,
    `order_date` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

INSERT INTO `products` (`id`, `name`, `brand`, `category`, `price`, `stock_quantity`) VALUES
(1, 'Smartwatch Pro GPS', 'FitTech', 'Wearables', 3499.00, 25),
(2, 'Noise Cancelling Earbuds 3', 'SoundMax', 'Audio', 2199.00, 40),
(3, 'Mechanical Gaming Keyboard', 'HyperKey', 'Accessories', 4299.00, 15)
ON DUPLICATE KEY UPDATE `price`=VALUES(`price`);


-- --------------------------------------------------------
-- 10. STYLEHUB FASHION STORE
-- --------------------------------------------------------
CREATE DATABASE IF NOT EXISTS `db_10_fashion` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `db_10_fashion`;

CREATE TABLE IF NOT EXISTS `apparel` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `title` VARCHAR(150) NOT NULL,
    `category` ENUM('Men', 'Women', 'Kids', 'Footwear') NOT NULL,
    `size` VARCHAR(10) NOT NULL,
    `color` VARCHAR(30) NOT NULL,
    `price` DECIMAL(8,2) NOT NULL,
    `stock` INT NOT NULL DEFAULT 10
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `orders` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `buyer_name` VARCHAR(100) NOT NULL,
    `address` TEXT NOT NULL,
    `apparel_id` INT NOT NULL,
    `total_paid` DECIMAL(8,2) NOT NULL,
    `status` ENUM('ordered', 'shipped', 'delivered') DEFAULT 'ordered',
    FOREIGN KEY (`apparel_id`) REFERENCES `apparel`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

INSERT INTO `apparel` (`id`, `title`, `category`, `size`, `color`, `price`, `stock`) VALUES
(1, 'Slim Fit Linen Shirt', 'Men', 'L', 'Sky Blue', 1299.00, 20),
(2, 'Ethnic Embroidered Kurti', 'Women', 'M', 'Maroon', 1499.00, 30),
(3, 'Denim Casual Jacket', 'Men', 'XL', 'Washed Indigo', 2499.00, 12)
ON DUPLICATE KEY UPDATE `price`=VALUES(`price`);


-- --------------------------------------------------------
-- 11. HOME FINDER REAL ESTATE
-- --------------------------------------------------------
CREATE DATABASE IF NOT EXISTS `db_11_real_estate` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `db_11_real_estate`;

CREATE TABLE IF NOT EXISTS `properties` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `title` VARCHAR(150) NOT NULL,
    `property_type` ENUM('Apartment', 'Villa', 'Plot', 'Commercial') NOT NULL,
    `location` VARCHAR(100) NOT NULL,
    `price` DECIMAL(12,2) NOT NULL,
    `bedrooms` INT NOT NULL DEFAULT 2,
    `area_sqft` INT NOT NULL,
    `status` ENUM('available', 'sold', 'under_offer') DEFAULT 'available'
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `inquiries` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `client_name` VARCHAR(100) NOT NULL,
    `client_phone` VARCHAR(20) NOT NULL,
    `property_id` INT NOT NULL,
    `message` TEXT DEFAULT NULL,
    `inquiry_date` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`property_id`) REFERENCES `properties`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

INSERT INTO `properties` (`id`, `title`, `property_type`, `location`, `price`, `bedrooms`, `area_sqft`) VALUES
(1, 'Luxury 3BHK Gated Community Apartment', 'Apartment', 'OMR, Chennai', 7500000.00, 3, 1450),
(2, 'Independent Serene 4BHK Villa', 'Villa', 'ECR, Chennai', 16000000.00, 4, 2800)
ON DUPLICATE KEY UPDATE `price`=VALUES(`price`);


-- --------------------------------------------------------
-- 12. INSUREEASY INSURANCE PORTAL
-- --------------------------------------------------------
CREATE DATABASE IF NOT EXISTS `db_12_insurance` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `db_12_insurance`;

CREATE TABLE IF NOT EXISTS `policies` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `policy_name` VARCHAR(100) NOT NULL,
    `category` ENUM('Health', 'Life', 'Motor', 'Home') NOT NULL,
    `coverage_amount` DECIMAL(12,2) NOT NULL,
    `annual_premium` DECIMAL(10,2) NOT NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `proposals` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `applicant_name` VARCHAR(100) NOT NULL,
    `applicant_dob` DATE NOT NULL,
    `applicant_phone` VARCHAR(20) NOT NULL,
    `policy_id` INT NOT NULL,
    `nominee_name` VARCHAR(100) NOT NULL,
    `status` ENUM('under_review', 'approved', 'rejected') DEFAULT 'under_review',
    FOREIGN KEY (`policy_id`) REFERENCES `policies`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

INSERT INTO `policies` (`id`, `policy_name`, `category`, `coverage_amount`, `annual_premium`) VALUES
(1, 'Family Super Health Shield', 'Health', 1000000.00, 14500.00),
(2, 'Term Life Secured Protection', 'Life', 5000000.00, 9800.00)
ON DUPLICATE KEY UPDATE `annual_premium`=VALUES(`annual_premium`);


-- --------------------------------------------------------
-- 13. BLOSSOM GIFTS STORE
-- --------------------------------------------------------
CREATE DATABASE IF NOT EXISTS `db_13_blossom_gifts` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `db_13_blossom_gifts`;

CREATE TABLE IF NOT EXISTS `gift_items` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(120) NOT NULL,
    `category` ENUM('Fresh Flowers', 'Gift Hampers', 'Chocolates', 'Personalized') NOT NULL,
    `price` DECIMAL(8,2) NOT NULL,
    `in_stock` TINYINT(1) DEFAULT 1
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `deliveries` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `sender_name` VARCHAR(100) NOT NULL,
    `recipient_name` VARCHAR(100) NOT NULL,
    `shipping_address` TEXT NOT NULL,
    `gift_id` INT NOT NULL,
    `greeting_note` VARCHAR(255) DEFAULT NULL,
    `delivery_date` DATE NOT NULL,
    `status` ENUM('placed', 'packed', 'dispatched', 'delivered') DEFAULT 'placed',
    FOREIGN KEY (`gift_id`) REFERENCES `gift_items`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

INSERT INTO `gift_items` (`id`, `name`, `category`, `price`) VALUES
(1, 'Midnight Crimson Roses Bouquet (12 Pcs)', 'Fresh Flowers', 699.00),
(2, 'Ferrero Rocher & Teddy Luxury Combo', 'Gift Hampers', 1299.00),
(3, 'Custom Engraved Wooden Photo Frame', 'Personalized', 849.00)
ON DUPLICATE KEY UPDATE `price`=VALUES(`price`);


-- --------------------------------------------------------
-- 14. RENEWMARKET USED GOODS MARKETPLACE
-- --------------------------------------------------------
CREATE DATABASE IF NOT EXISTS `db_14_renewmarket` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `db_14_renewmarket`;

CREATE TABLE IF NOT EXISTS `listings` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `item_title` VARCHAR(150) NOT NULL,
    `category` VARCHAR(50) NOT NULL,
    `condition_rating` ENUM('Like New', 'Gently Used', 'Fair', 'Refurbished') NOT NULL,
    `price` DECIMAL(10,2) NOT NULL,
    `seller_name` VARCHAR(100) NOT NULL,
    `seller_contact` VARCHAR(20) NOT NULL,
    `status` ENUM('active', 'sold', 'reserved') DEFAULT 'active'
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `inquiries` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `listing_id` INT NOT NULL,
    `buyer_name` VARCHAR(100) NOT NULL,
    `offer_amount` DECIMAL(10,2) NOT NULL,
    `buyer_message` TEXT DEFAULT NULL,
    FOREIGN KEY (`listing_id`) REFERENCES `listings`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

INSERT INTO `listings` (`id`, `item_title`, `category`, `condition_rating`, `price`, `seller_name`, `seller_contact`) VALUES
(1, 'Sony WH-1000XM4 Headphones (Box included)', 'Electronics', 'Like New', 12500.00, 'Karthik S', '9840112233'),
(2, 'Study Desk with Ergonomic Chair', 'Furniture', 'Gently Used', 3800.00, 'Sneha M', '9840998877')
ON DUPLICATE KEY UPDATE `price`=VALUES(`price`);


-- --------------------------------------------------------
-- 15. EASYTOLL TOLL PAYMENT & FASTAG
-- --------------------------------------------------------
CREATE DATABASE IF NOT EXISTS `db_15_easytoll` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `db_15_easytoll`;

CREATE TABLE IF NOT EXISTS `toll_plazas` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `plaza_name` VARCHAR(100) NOT NULL,
    `highway_code` VARCHAR(20) NOT NULL,
    `rate_car` DECIMAL(6,2) NOT NULL,
    `rate_truck` DECIMAL(6,2) NOT NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `toll_passages` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `vehicle_number` VARCHAR(15) NOT NULL,
    `plaza_id` INT NOT NULL,
    `vehicle_class` ENUM('Car/Jeep', 'LCV', 'Bus/Truck') NOT NULL,
    `fee_charged` DECIMAL(6,2) NOT NULL,
    `fastag_tag_id` VARCHAR(30) DEFAULT NULL,
    `passed_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`plaza_id`) REFERENCES `toll_plazas`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

INSERT INTO `toll_plazas` (`id`, `plaza_name`, `highway_code`, `rate_car`, `rate_truck`) VALUES
(1, 'Paranur Toll Plaza', 'NH-45', 70.00, 230.00),
(2, 'Nemili Toll Plaza', 'NH-4', 85.00, 290.00)
ON DUPLICATE KEY UPDATE `rate_car`=VALUES(`rate_car`);


-- --------------------------------------------------------
-- 16. COMPLAINEASE GRIEVANCE REDRESSAL
-- --------------------------------------------------------
CREATE DATABASE IF NOT EXISTS `db_16_complainease` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `db_16_complainease`;

CREATE TABLE IF NOT EXISTS `departments` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `dept_name` VARCHAR(100) NOT NULL,
    `officer_in_charge` VARCHAR(100) NOT NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `complaints` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `ticket_id` VARCHAR(15) NOT NULL UNIQUE,
    `citizen_name` VARCHAR(100) NOT NULL,
    `citizen_email` VARCHAR(120) NOT NULL,
    `department_id` INT NOT NULL,
    `complaint_subject` VARCHAR(150) NOT NULL,
    `details` TEXT NOT NULL,
    `status` ENUM('registered', 'investigating', 'resolved', 'closed') DEFAULT 'registered',
    `lodged_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`department_id`) REFERENCES `departments`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

INSERT INTO `departments` (`id`, `dept_name`, `officer_in_charge`) VALUES
(1, 'Water Supply and Drainage', 'Er. M. Senthil'),
(2, 'Electricity Board (TNEB)', 'Er. K. Raman'),
(3, 'Roads & Infrastructure', 'Er. V. Meenakshi')
ON DUPLICATE KEY UPDATE `dept_name`=VALUES(`dept_name`);


-- --------------------------------------------------------
-- 17. VOTEONLINE VOTING DEMO
-- --------------------------------------------------------
CREATE DATABASE IF NOT EXISTS `db_17_voteonline` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `db_17_voteonline`;

CREATE TABLE IF NOT EXISTS `candidates` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `party` VARCHAR(100) NOT NULL,
    `symbol` VARCHAR(30) NOT NULL,
    `votes_tally` INT NOT NULL DEFAULT 0
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `voters` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `voter_card_number` VARCHAR(20) NOT NULL UNIQUE,
    `voter_name` VARCHAR(100) NOT NULL,
    `has_voted` TINYINT(1) DEFAULT 0,
    `voted_at` DATETIME DEFAULT NULL
) ENGINE=InnoDB;

INSERT INTO `candidates` (`id`, `name`, `party`, `symbol`, `votes_tally`) VALUES
(1, 'Dr. Arul Kumar', 'Progressive Citizens Party', 'Rising Sun', 142),
(2, 'Deepa Mohan', 'National United Front', 'Torch', 128),
(3, 'Suresh Babu', 'Green Earth Alliance', 'Leaf', 85)
ON DUPLICATE KEY UPDATE `votes_tally`=VALUES(`votes_tally`);

INSERT INTO `voters` (`id`, `voter_card_number`, `voter_name`, `has_voted`) VALUES
(1, 'TN-2026-001', 'Pandi Rajan', 0),
(2, 'TN-2026-002', 'Bala Subramanian', 1)
ON DUPLICATE KEY UPDATE `voter_name`=VALUES(`voter_name`);


-- --------------------------------------------------------
-- 18. AGRIMART AGRICULTURAL MARKETPLACE
-- --------------------------------------------------------
CREATE DATABASE IF NOT EXISTS `db_18_agrimart` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `db_18_agrimart`;

CREATE TABLE IF NOT EXISTS `farm_produce` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `crop_name` VARCHAR(100) NOT NULL,
    `category` ENUM('Grains', 'Pulses', 'Vegetables', 'Fruits', 'Seeds') NOT NULL,
    `quantity_quintals` DECIMAL(8,2) NOT NULL,
    `price_per_quintal` DECIMAL(10,2) NOT NULL,
    `farmer_name` VARCHAR(100) NOT NULL,
    `location` VARCHAR(100) NOT NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `orders` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `produce_id` INT NOT NULL,
    `buyer_name` VARCHAR(100) NOT NULL,
    `buyer_contact` VARCHAR(20) NOT NULL,
    `ordered_qty` DECIMAL(8,2) NOT NULL,
    `total_cost` DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (`produce_id`) REFERENCES `farm_produce`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

INSERT INTO `farm_produce` (`id`, `crop_name`, `category`, `quantity_quintals`, `price_per_quintal`, `farmer_name`, `location`) VALUES
(1, 'Organic Ponni Paddy Rice', 'Grains', 150.00, 2400.00, 'Thiru. Velusamy', 'Thanjavur'),
(2, 'Fresh Red Onion (Shallots)', 'Vegetables', 60.00, 3100.00, 'Thiru. Murugan', 'Perambalur')
ON DUPLICATE KEY UPDATE `price_per_quintal`=VALUES(`price_per_quintal`);


-- --------------------------------------------------------
-- 19. VETCURE PET CARE & ANIMAL CLINIC
-- --------------------------------------------------------
CREATE DATABASE IF NOT EXISTS `db_19_vetcure` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `db_19_vetcure`;

CREATE TABLE IF NOT EXISTS `vets` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `doctor_name` VARCHAR(100) NOT NULL,
    `specialization` VARCHAR(100) NOT NULL,
    `consultation_fee` DECIMAL(8,2) NOT NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `pet_appointments` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `pet_name` VARCHAR(50) NOT NULL,
    `pet_species` ENUM('Dog', 'Cat', 'Bird', 'Rabbit', 'Other') NOT NULL,
    `owner_name` VARCHAR(100) NOT NULL,
    `owner_phone` VARCHAR(20) NOT NULL,
    `vet_id` INT NOT NULL,
    `appointment_date` DATE NOT NULL,
    `reason` TEXT DEFAULT NULL,
    `status` ENUM('scheduled', 'treated', 'cancelled') DEFAULT 'scheduled',
    FOREIGN KEY (`vet_id`) REFERENCES `vets`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

INSERT INTO `vets` (`id`, `doctor_name`, `specialization`, `consultation_fee`) VALUES
(1, 'Dr. Shweta Rao', 'Canine & Feline Medicine', 450.00),
(2, 'Dr. Balaji K', 'Veterinary Surgery & Orthopedics', 600.00)
ON DUPLICATE KEY UPDATE `consultation_fee`=VALUES(`consultation_fee`);


-- --------------------------------------------------------
-- 20. RENTRIDE VEHICLE RENTAL SYSTEM
-- --------------------------------------------------------
CREATE DATABASE IF NOT EXISTS `db_20_rentride` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `db_20_rentride`;

CREATE TABLE IF NOT EXISTS `vehicles` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `model_name` VARCHAR(100) NOT NULL,
    `vehicle_type` ENUM('Hatchback', 'Sedan', 'SUV', 'Motorbike') NOT NULL,
    `transmission` ENUM('Manual', 'Automatic') NOT NULL,
    `rate_per_day` DECIMAL(8,2) NOT NULL,
    `reg_plate` VARCHAR(20) NOT NULL UNIQUE,
    `status` ENUM('available', 'rented', 'servicing') DEFAULT 'available'
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `rentals` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `customer_name` VARCHAR(100) NOT NULL,
    `customer_phone` VARCHAR(20) NOT NULL,
    `license_number` VARCHAR(30) NOT NULL,
    `vehicle_id` INT NOT NULL,
    `start_date` DATE NOT NULL,
    `end_date` DATE NOT NULL,
    `total_charge` DECIMAL(10,2) NOT NULL,
    `rental_status` ENUM('ongoing', 'returned', 'cancelled') DEFAULT 'ongoing',
    FOREIGN KEY (`vehicle_id`) REFERENCES `vehicles`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

INSERT INTO `vehicles` (`id`, `model_name`, `vehicle_type`, `transmission`, `rate_per_day`, `reg_plate`) VALUES
(1, 'Hyundai Creta SX', 'SUV', 'Automatic', 2800.00, 'TN-07-CP-8921'),
(2, 'Maruti Suzuki Swift', 'Hatchback', 'Manual', 1400.00, 'TN-07-BN-4432'),
(3, 'Royal Enfield Classic 350', 'Motorbike', 'Manual', 900.00, 'TN-07-DQ-7711')
ON DUPLICATE KEY UPDATE `rate_per_day`=VALUES(`rate_per_day`);

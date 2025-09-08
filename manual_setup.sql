-- Manual SQL Setup for Railway Database
-- Run this in Railway Database console if commands fail

-- Insert Admin User
INSERT IGNORE INTO users (id, name, email, password, is_admin, created_at, updated_at) VALUES 
(1, 'Admin User', 'admin@email.com', '$2y$12$OiP2UF66w5DyZ2aomWPU3.bjeskEnr5HSs9dahYbVTXfCX/njCHae', 1, NOW(), NOW());

-- Insert Model Items
INSERT IGNORE INTO model_items (id, model_code, model_year, item_name, created_at, updated_at) VALUES 
(1, 'FFVV', '2026', 'ITEM PERTAMA', NOW(), NOW()),
(2, 'FFVV', '2026', 'ITEM KEDUA', NOW(), NOW()),
(3, 'FFVV', '2026', 'ITEM KETIGA', NOW(), NOW());

-- Insert Downtime Classifications
INSERT IGNORE INTO downtime_classifications (id, downtime_classification, created_at, updated_at) VALUES 
(1, 'Planned Downtime', NOW(), NOW()),
(2, 'Gomi', NOW(), NOW()),
(3, 'Kiriko', NOW(), NOW()),
(5, 'Dent', NOW(), NOW()),
(6, 'Scratch', NOW(), NOW()),
(7, 'Crack', NOW(), NOW()),
(8, 'Necking', NOW(), NOW()),
(9, 'Burry', NOW(), NOW()),
(10, 'Ding', NOW(), NOW());

-- Insert Process Names
INSERT IGNORE INTO process_names (id, process_name, created_at, updated_at) VALUES 
(1, 'Line All', NOW(), NOW()),
(16, 'OP.10', NOW(), NOW()),
(18, 'OP.20', NOW(), NOW()),
(20, 'OP.30', NOW(), NOW()),
(22, 'OP.40', NOW(), NOW());

-- Insert Sample Production Data
INSERT IGNORE INTO table_productions (id, reporter, `group`, date, fy_n, shift, line, start_time, finish_time, total_prod_time, model, model_year, spm, item_name, coil_no, plan_a, plan_b, ok_a, ok_b, rework_a, rework_b, scrap_a, scrap_b, sample_a, sample_b, rework_exp, scrap_exp, trial_sample_exp, created_at, updated_at) VALUES 
(1, 'Admin User', 'A', '2025-09-08', 'FY25-6', 'day', 'Line-A', '08:00:00', '16:00:00', 480, 'FFVV', '2026', 10.5, 'FFVV-ITEM PERTAMA', 'SAMPLE001', 500, 500, 475, 470, 15, 18, 10, 12, 0, 0, 'Minor surface defects', 'Material crack', 'None', NOW(), NOW()),
(2, 'Admin User', 'B', '2025-09-08', 'FY25-6', 'night', 'Line-A', '20:00:00', '04:00:00', 480, 'FFVV', '2026', 9.8, 'FFVV-ITEM KEDUA', 'SAMPLE002', 450, 450, 430, 425, 12, 15, 8, 10, 0, 0, 'Dimensional issues', 'Burry defects', 'None', NOW(), NOW());

-- Insert Sample Downtime Data
INSERT IGNORE INTO table_downtimes (table_production_id, reporter, `group`, date, fy_n, shift, line, model, model_year, item_name, coil_no, time_from, time_until, total_time, process_name, dt_category, downtime_type, dt_classification, problem_description, root_cause, counter_measure, pic, status, created_at, updated_at) VALUES 
(1, 'Admin User', 'A', '2025-09-08', 'FY25-6', 'day', 'Line-A', 'FFVV', '2026', 'FFVV-ITEM PERTAMA', 'SAMPLE001', '10:15:00', '10:30:00', 15, 'OP.10', 'Equipment', 'Downtime', 'Crack', 'Die crack detected', 'Material hardness', 'Die replacement', 'tooling', 'close', NOW(), NOW());

-- Insert Sample Defect Data
INSERT IGNORE INTO table_defects (table_production_id, reporter, `group`, date, fy_n, shift, line, model, model_year, item_name, coil_no, defect_category, defect_name, defect_qty_a, defect_qty_b, defect_area, created_at, updated_at) VALUES 
(1, 'Admin User', 'A', '2025-09-08', 'FY25-6', 'day', 'Line-A', 'FFVV', '2026', 'FFVV-ITEM PERTAMA', 'SAMPLE001', 'inline', 'Gomi', 5, 3, 'K7', NOW(), NOW());

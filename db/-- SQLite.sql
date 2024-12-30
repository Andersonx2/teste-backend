-- -- -- SQLite
-- -- -- SELECT p.*, c.title as category
-- -- -- FROM product p
-- -- -- LEFT JOIN product_category pc ON pc.product_id = p.id
-- -- -- LEFT JOIN category c ON c.id = pc.category_id
-- -- -- WHERE p.id = :id

-- SELECT * FROM category;

-- SELECT * FROM product; 

-- SELECT * FROM product_category;

--          SELECT 
--             p.id AS product_id, 
--             p.title, 
--             p.price, 
--             p.active, 
--             p.created_at, 
--             c.id AS category_id, 
--             c.title AS category_name
--         FROM 
--             product p
--         JOIN 
--             product_category pc ON p.id = pc.product_id
--         JOIN 
--             category c ON pc.cat_id = c.id
--         WHERE 
--             p.id = 10

-- --             PRAGMA table_info(category);
-- SELECT * FROM company; 

-- PRAGMA table_info(company);





--             SELECT p.*, c.title as category
--             FROM product p
--             INNER JOIN product_category pc ON pc.product_id = p.id
--             INNER JOIN category c ON c.id = pc.id
--             WHERE p.company_id = 1



--             SELECT p.*, c.title as category
--             FROM product p
--             INNER JOIN product_category pc ON pc.product_id = p.id
--             INNER JOIN category c ON c.id = pc.id
--             WHERE p.company_id = 1;


-- -- SELECT  * FROM product;


-- SELECT p.*, GROUP_CONCAT(c.title) as categories
-- FROM product p
-- INNER JOIN product_category pc ON pc.product_id = p.id
-- INNER JOIN category c ON c.id = pc.category_id
-- WHERE p.company_id = 1
-- GROUP BY p.id;








-- SELECT p.*, c.title as category
-- FROM product p
-- INNER JOIN product_category pc ON pc.product_id = p.id
-- INNER JOIN category c ON c.id = pc.id
-- WHERE p.company_id = 1 


SELECT 
    p.id AS product_id, 
    p.title, 
    p.price, 
    p.active, 
    p.created_at, 
    c.id AS category_id, 
    c.title AS category_name
FROM 
    product p
JOIN 
    product_category pc ON p.id = pc.product_id
JOIN 
    category c ON pc.cat_id = c.id
WHERE 
    p.id = 10; 


 SELECT 
    p.id AS product_id, 
    p.title, 
    p.price, 
    p.active, 
    p.created_at, 
    c.id AS category_id, 
    c.title AS category_name
FROM 
    product p
JOIN 
    product_category pc ON p.id = pc.product_id
JOIN 
    category c ON pc.cat_id = c.id;

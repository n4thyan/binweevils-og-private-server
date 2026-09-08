-- Repair pet bed ownership rows created by the partial integration.
-- Canonical Bin Pets uses one colour-tinted f_petBasket2 asset (item 2634),
-- not nonexistent f_petBed_<colour> assets.
UPDATE weevilitems wi
JOIN pets p ON p.bedID = wi.ID
SET wi.itemId = 2634,
    wi.category = 993,
    wi.configName = 'f_petBasket2',
    wi.internalCategory = 1,
    wi.colour = IF(wi.colour = 0, 16759552, wi.colour)
WHERE wi.itemId BETWEEN 2855 AND 2863
   OR wi.configName LIKE 'f_petBed_%';

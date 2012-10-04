UPDATE dt_messages m
INNER JOIN dt_pays p
ON m.pays = p.code_iso
SET m.id_pays = p.id
WHERE p.code_iso = m.pays
AND p.code_iso <> ''

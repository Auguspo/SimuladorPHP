USE simulator_db;

-- 1. Agregar la columna de tracción a la tabla sessions
ALTER TABLE sessions 
ADD COLUMN traction_mode ENUM('2H', '4H', '4L', 'Indefinido') NOT NULL DEFAULT 'Indefinido' 
AFTER participant_comment;

-- 2. Restringir la columna stimulus a sólo los valores válidos
ALTER TABLE session_events 
MODIFY COLUMN stimulus ENUM('Freno (LED)', 'Acelerador (LED)', 'Freno (Bocina)', 'Acelerador (Bocina)', 'Boton 1', 'Boton 2', 'Boton 3', 'Boton 4', '-') NOT NULL;

-- Importa este archivo dentro de la base de datos ya creada en Hostinger:
-- u404968876_gameExcel
--
-- En hosting compartido normalmente no hace falta ni conviene ejecutar CREATE DATABASE.

CREATE TABLE IF NOT EXISTS users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(40) NOT NULL UNIQUE,
    email VARCHAR(120) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NULL,
    is_vip TINYINT UNSIGNED NOT NULL DEFAULT 0,
    email_verified TINYINT UNSIGNED NOT NULL DEFAULT 0,
    oauth_provider VARCHAR(20) NULL,
    oauth_id VARCHAR(255) NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_oauth (oauth_provider, oauth_id)
);

CREATE TABLE IF NOT EXISTS email_verifications (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    token VARCHAR(64) NOT NULL UNIQUE,
    expires_at DATETIME NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_emailverif_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS levels (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    numero INT NOT NULL UNIQUE,
    dificultad VARCHAR(30) NOT NULL,
    categoria VARCHAR(80) NOT NULL,
    titulo VARCHAR(120) NOT NULL,
    consigna VARCHAR(255) NOT NULL,
    respuesta_correcta VARCHAR(255) NOT NULL,
    respuestas_alternativas TEXT NULL,
    formula_target VARCHAR(10) NOT NULL,
    points_reward INT NOT NULL DEFAULT 10,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS progress (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL UNIQUE,
    nivel_actual INT NOT NULL DEFAULT 1,
    puntos INT NOT NULL DEFAULT 0,
    vidas INT NOT NULL DEFAULT 5,
    racha_actual INT NOT NULL DEFAULT 0,
    niveles_completados INT NOT NULL DEFAULT 0,
    last_life_lost_at DATETIME NULL,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_progress_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS user_level_status (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    level_id INT UNSIGNED NOT NULL,
    attempts INT NOT NULL DEFAULT 0,
    best_formula VARCHAR(255) NULL,
    completed_at DATETIME NULL,
    score_earned INT NOT NULL DEFAULT 0,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uniq_user_level (user_id, level_id),
    CONSTRAINT fk_user_level_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE,
    CONSTRAINT fk_user_level_level FOREIGN KEY (level_id) REFERENCES levels (id) ON DELETE CASCADE
);

DELETE FROM user_level_status;
DELETE FROM progress;
DELETE FROM levels;

INSERT INTO levels (numero, dificultad, categoria, titulo, consigna, respuesta_correcta, respuestas_alternativas, formula_target, points_reward) VALUES
(1, 'Básico', 'SUMA', 'Suma inicial', 'Suma los valores de B2 y B3 en la celda E2.', '=SUMA(B2:B3)', '=B2+B3', 'E2', 10),
(2, 'Básico', 'SUMA', 'Total de ventas', 'Calcula el total del rango B2:B6 en la celda E3.', '=SUMA(B2:B6)', '=B2+B3+B4+B5+B6', 'E3', 10),
(3, 'Básico', 'RESTA', 'Diferencia simple', 'Resta B2 a C2 y escribe la fórmula en E4.', '=C2-B2', NULL, 'E4', 10),
(4, 'Básico', 'MULTIPLICACIÓN', 'Producto unitario', 'Multiplica B3 por C3 en la celda E5.', '=B3*C3', '=PRODUCTO(B3,C3)', 'E5', 10),
(5, 'Básico', 'DIVISIÓN', 'Razón básica', 'Divide D4 entre C4 y coloca la fórmula en E6.', '=D4/C4', NULL, 'E6', 10),
(6, 'Básico', 'Referencias', 'Referencia directa', 'Muestra el contenido de A2 usando una fórmula en E2.', '=A2', NULL, 'E2', 11),
(7, 'Básico', 'SUMA', 'Bloque corto', 'Suma el rango C2:C4 en la celda E3.', '=SUMA(C2:C4)', '=C2+C3+C4', 'E3', 11),
(8, 'Básico', 'RESTA', 'Ajuste de inventario', 'Resta E3 a D3 con una fórmula en E4.', '=D3-E3', NULL, 'E4', 11),
(9, 'Básico', 'MULTIPLICACIÓN', 'Ingreso por fila', 'Multiplica B4 por C4 y escribe el resultado con fórmula en E5.', '=B4*C4', '=PRODUCTO(B4,C4)', 'E5', 11),
(10, 'Básico', 'DIVISIÓN', 'Comparación de métricas', 'Divide C5 entre B5 en la celda E6.', '=C5/B5', NULL, 'E6', 11),
(11, 'Básico', 'SUMA', 'Suma horizontal', 'Suma B6:D6 en la celda E2.', '=SUMA(B6:D6)', '=B6+C6+D6', 'E2', 12),
(12, 'Básico', 'Operaciones básicas', 'Balance rápido', 'Suma B2 y C2, luego resta D2, todo en E3.', '=B2+C2-D2', '=SUMA(B2,C2)-D2', 'E3', 12),
(13, 'Básico', 'Referencias', 'Referencia de resultado', 'Trae el contenido de E2 en la celda E4 usando fórmula.', '=E2', NULL, 'E4', 12),
(14, 'Básico', 'SUMA', 'Bloque rectangular', 'Suma todas las celdas de B2:D4 en E5.', '=SUMA(B2:D4)', NULL, 'E5', 12),
(15, 'Básico', 'Operaciones básicas', 'Costo combinado', 'Multiplica B5 por C5 y luego suma D5 en E6.', '=B5*C5+D5', '=PRODUCTO(B5,C5)+D5', 'E6', 12),
(16, 'Básico', 'Operaciones básicas', 'Promedio simple simulado', 'Suma B6 y C6, luego divide entre D6 en E2.', '=(B6+C6)/D6', '=SUMA(B6,C6)/D6', 'E2', 13),
(17, 'Básico', 'SUMA', 'Celdas alternas', 'Suma B2, B4 y B6 en la celda E3.', '=B2+B4+B6', '=SUMA(B2,B4,B6)', 'E3', 13),
(18, 'Básico', 'RESTA', 'Resta encadenada', 'Resta C5 y B5 a D5 en E4.', '=D5-C5-B5', NULL, 'E4', 13),
(19, 'Básico', 'Operaciones básicas', 'Multiplicación acumulada', 'Suma B2*C2 y B3*C3 en E5.', '=B2*C2+B3*C3', '=PRODUCTO(B2,C2)+PRODUCTO(B3,C3)', 'E5', 13),
(20, 'Básico', 'SUMA', 'Cierre del bloque básico', 'Suma B2:B6 y luego resta C2 en la celda E6.', '=SUMA(B2:B6)-C2', NULL, 'E6', 14),

(21, 'Intermedio 1', 'PROMEDIO', 'Promedio inicial', 'Calcula el promedio del rango B2:B6 en F2.', '=PROMEDIO(B2:B6)', NULL, 'F2', 15),
(22, 'Intermedio 1', 'MAX', 'Valor máximo', 'Encuentra el valor máximo de C2:C6 en F3.', '=MAX(C2:C6)', NULL, 'F3', 15),
(23, 'Intermedio 1', 'MIN', 'Valor mínimo', 'Obtén el valor mínimo de D2:D6 en F4.', '=MIN(D2:D6)', NULL, 'F4', 15),
(24, 'Intermedio 1', 'CONTAR', 'Conteo de datos', 'Cuenta cuántas celdas numéricas hay en B2:B10 en F5.', '=CONTAR(B2:B10)', NULL, 'F5', 15),
(25, 'Intermedio 1', 'PROMEDIO', 'Promedio horizontal', 'Calcula el promedio del rango B4:D4 en F6.', '=PROMEDIO(B4:D4)', NULL, 'F6', 15),
(26, 'Intermedio 1', 'MAX', 'Pico trimestral', 'Busca el valor máximo de B2:D5 en F7.', '=MAX(B2:D5)', NULL, 'F7', 16),
(27, 'Intermedio 1', 'MIN', 'Costo más bajo', 'Encuentra el mínimo de C2:C7 en F8.', '=MIN(C2:C7)', NULL, 'F8', 16),
(28, 'Intermedio 1', 'CONTAR', 'Conteo extendido', 'Cuenta los números del rango D2:D9 en F9.', '=CONTAR(D2:D9)', NULL, 'F9', 16),
(29, 'Intermedio 1', 'PROMEDIO', 'Asistencia media', 'Calcula el promedio de C3:C8 en F10.', '=PROMEDIO(C3:C8)', NULL, 'F10', 16),
(30, 'Intermedio 1', 'MAX', 'Mejor semana', 'Obtén el máximo de E2:E6 en F2.', '=MAX(E2:E6)', NULL, 'F2', 16),
(31, 'Intermedio 1', 'MIN', 'Temperatura mínima', 'Calcula el mínimo de B2:B8 en F3.', '=MIN(B2:B8)', NULL, 'F3', 17),
(32, 'Intermedio 1', 'CONTAR', 'Conteo compacto', 'Cuenta los valores numéricos de B2:E2 en F4.', '=CONTAR(B2:E2)', NULL, 'F4', 17),
(33, 'Intermedio 1', 'PROMEDIO', 'Promedio general', 'Promedia B2:D6 y escribe la fórmula en F5.', '=PROMEDIO(B2:D6)', NULL, 'F5', 17),
(34, 'Intermedio 1', 'MAX', 'Inventario más alto', 'Busca el mayor valor de D2:D7 en F6.', '=MAX(D2:D7)', NULL, 'F6', 17),
(35, 'Intermedio 1', 'MIN', 'Descuento mínimo', 'Calcula el mínimo de E2:E7 en F7.', '=MIN(E2:E7)', NULL, 'F7', 17),
(36, 'Intermedio 1', 'CONTAR', 'Conteo mixto', 'Cuenta cuántos números hay en B2:C9 en F8.', '=CONTAR(B2:C9)', NULL, 'F8', 18),
(37, 'Intermedio 1', 'PROMEDIO', 'Mini promedio', 'Obtén el promedio de B2:B4 en F9.', '=PROMEDIO(B2:B4)', NULL, 'F9', 18),
(38, 'Intermedio 1', 'MAX', 'Máximo en fila', 'Busca el valor más alto entre C6:F6 en F10.', '=MAX(C6:F6)', NULL, 'F10', 18),
(39, 'Intermedio 1', 'MIN', 'Mínimo combinado', 'Calcula el mínimo del rango B3:D5 en F2.', '=MIN(B3:D5)', NULL, 'F2', 18),
(40, 'Intermedio 1', 'CONTAR', 'Cierre de conteo', 'Cuenta las celdas numéricas de C2:C8 en F3.', '=CONTAR(C2:C8)', NULL, 'F3', 19),

(41, 'Intermedio 2', 'SI', 'Aprobado o refuerzo', 'Si B2 es mayor o igual a 70 muestra "Aprobado" y si no "Reforzar" en E2.', '=SI(B2>=70,"Aprobado","Reforzar")', NULL, 'E2', 20),
(42, 'Intermedio 2', 'SI', 'Control de stock', 'Si C2 es menor que 10 muestra "Pedir"; de lo contrario "Ok" en E3.', '=SI(C2<10,"Pedir","Ok")', NULL, 'E3', 20),
(43, 'Intermedio 2', 'SI', 'Bono de ventas', 'Si D2 es mayor o igual a 1000 muestra "Bono"; si no "Sin bono" en E4.', '=SI(D2>=1000,"Bono","Sin bono")', NULL, 'E4', 20),
(44, 'Intermedio 2', 'SI', 'Asistencia perfecta', 'Si B3 es igual a 100 muestra "Perfecta"; si no "Pendiente" en E5.', '=SI(B3=100,"Perfecta","Pendiente")', NULL, 'E5', 20),
(45, 'Intermedio 2', 'SUMAR.SI', 'Ventas del norte', 'Suma los valores de B2:B8 cuando A2:A8 sea "Norte" en D2.', '=SUMAR.SI(A2:A8,"Norte",B2:B8)', NULL, 'D2', 21),
(46, 'Intermedio 2', 'SUMAR.SI', 'Resultados sobre 50', 'Suma D2:D9 solo cuando C2:C9 sea mayor o igual a 50 en D3.', '=SUMAR.SI(C2:C9,">=50",D2:D9)', NULL, 'D3', 21),
(47, 'Intermedio 2', 'PROMEDIO.SI', 'Media de marketing', 'Calcula el promedio de B2:B7 donde A2:A7 sea "Marketing" en C2.', '=PROMEDIO.SI(A2:A7,"Marketing",B2:B7)', NULL, 'C2', 21),
(48, 'Intermedio 2', 'PROMEDIO.SI', 'Promedio de aprobados', 'Promedia D2:D8 cuando C2:C8 sea mayor o igual a 70 en D4.', '=PROMEDIO.SI(C2:C8,">=70",D2:D8)', NULL, 'D4', 21),
(49, 'Intermedio 2', 'SI', 'Descuento activo', 'Si D2 es mayor que 0 muestra "Descuento" y si no "Precio completo" en E6.', '=SI(D2>0,"Descuento","Precio completo")', NULL, 'E6', 22),
(50, 'Intermedio 2', 'SUMAR.SI', 'Total laptops', 'Suma C2:C10 solo cuando B2:B10 sea "Laptop" en C10.', '=SUMAR.SI(B2:B10,"Laptop",C2:C10)', NULL, 'C10', 22),
(51, 'Intermedio 2', 'PROMEDIO.SI', 'Turno A', 'Calcula el promedio de C2:C9 donde B2:B9 sea "Turno A" en C9.', '=PROMEDIO.SI(B2:B9,"Turno A",C2:C9)', NULL, 'C9', 22),
(52, 'Intermedio 2', 'SI', 'Comparación utilidad y costo', 'Si D4 es mayor que C4 muestra "Gana"; si no "Pierde" en E2.', '=SI(D4>C4,"Gana","Pierde")', NULL, 'E2', 22),
(53, 'Intermedio 2', 'SUMAR.SI', 'Checklist positivo', 'Suma B2:B8 solo cuando A2:A8 sea "Si" en B8.', '=SUMAR.SI(A2:A8,"Si",B2:B8)', NULL, 'B8', 23),
(54, 'Intermedio 2', 'PROMEDIO.SI', 'Clientes activos', 'Promedia B2:B9 solo cuando A2:A9 sea "Activo" en B9.', '=PROMEDIO.SI(A2:A9,"Activo",B2:B9)', NULL, 'B9', 23),
(55, 'Intermedio 2', 'SI', 'Alerta crítica', 'Si C5 es menor o igual a 5 muestra "Critico"; si no "Estable" en E3.', '=SI(C5<=5,"Critico","Estable")', NULL, 'E3', 23),
(56, 'Intermedio 2', 'SUMAR.SI', 'Pedidos rojos', 'Suma D2:D8 cuando C2:C8 sea "Rojo" en D8.', '=SUMAR.SI(C2:C8,"Rojo",D2:D8)', NULL, 'D8', 23),
(57, 'Intermedio 2', 'PROMEDIO.SI', 'Rendimiento alto', 'Promedia D2:D8 solo cuando C2:C8 sea mayor o igual a 80 en C8.', '=PROMEDIO.SI(C2:C8,">=80",D2:D8)', NULL, 'C8', 24),
(58, 'Intermedio 2', 'SI', 'Estado del pago', 'Si E6 es igual a "Pago" muestra "Cerrar" y si no "Seguimiento" en E4.', '=SI(E6="Pago","Cerrar","Seguimiento")', NULL, 'E4', 24),
(59, 'Intermedio 2', 'SUMAR.SI', 'Ventas del oeste', 'Suma B2:B10 cuando A2:A10 sea "Oeste" en D9.', '=SUMAR.SI(A2:A10,"Oeste",B2:B10)', NULL, 'D9', 24),
(60, 'Intermedio 2', 'PROMEDIO.SI', 'Promedio positivo', 'Calcula el promedio de D2:D9 solo para valores mayores que 0 en D10.', '=PROMEDIO.SI(D2:D9,">0")', NULL, 'D10', 25),

(61, 'Avanzado 1', 'BUSCARV', 'Buscar producto', 'Usa BUSCARV para traer la segunda columna para el código en H2.', '=BUSCARV(H2,A2:D8,2,FALSO)', '=BUSCARV(H2,A2:D8,2,0)', 'I2', 26),
(62, 'Avanzado 1', 'BUSCARV', 'Buscar categoría', 'Usa BUSCARV para devolver la tercera columna con el código de H2.', '=BUSCARV(H2,A2:D8,3,FALSO)', '=BUSCARV(H2,A2:D8,3,0)', 'I3', 26),
(63, 'Avanzado 1', 'BUSCARV', 'Buscar precio', 'Obtén la cuarta columna con BUSCARV usando H3 como referencia.', '=BUSCARV(H3,A2:D8,4,FALSO)', '=BUSCARV(H3,A2:D8,4,0)', 'I4', 26),
(64, 'Avanzado 1', 'BUSCARV', 'Precio desde G2', 'Devuelve la tercera columna del rango A2:C7 buscando G2.', '=BUSCARV(G2,A2:C7,3,FALSO)', '=BUSCARV(G2,A2:C7,3,0)', 'G2', 27),
(65, 'Avanzado 1', 'BUSCARV', 'Producto desde F2', 'Busca F2 en A2:D6 y devuelve la segunda columna.', '=BUSCARV(F2,A2:D6,2,FALSO)', '=BUSCARV(F2,A2:D6,2,0)', 'G3', 27),
(66, 'Avanzado 1', 'BUSCARV', 'Quinta columna', 'Usa H4 para devolver la quinta columna de A2:E8.', '=BUSCARV(H4,A2:E8,5,FALSO)', '=BUSCARV(H4,A2:E8,5,0)', 'I5', 27),
(67, 'Avanzado 1', 'BUSCARX', 'Buscar con BUSCARX', 'Usa BUSCARX para encontrar H2 en A2:A8 y devolver C2:C8.', '=BUSCARX(H2,A2:A8,C2:C8)', '=XLOOKUP(H2,A2:A8,C2:C8)', 'I2', 28),
(68, 'Avanzado 1', 'BUSCARX', 'Categoría directa', 'Busca G2 en A2:A7 y devuelve D2:D7 con BUSCARX.', '=BUSCARX(G2,A2:A7,D2:D7)', '=XLOOKUP(G2,A2:A7,D2:D7)', 'G4', 28),
(69, 'Avanzado 1', 'BUSCARX', 'Responsable por código', 'Busca F2 en B2:B8 y devuelve E2:E8 usando BUSCARX.', '=BUSCARX(F2,B2:B8,E2:E8)', '=XLOOKUP(F2,B2:B8,E2:E8)', 'G5', 28),
(70, 'Avanzado 1', 'Errores', 'Controla un error de búsqueda', 'Envuelve BUSCARV con SI.ERROR para mostrar "Sin dato" cuando no exista coincidencia.', '=SI.ERROR(BUSCARV(H2,A2:D8,4,FALSO),"Sin dato")', '=SI.ERROR(BUSCARV(H2,A2:D8,4,0),"Sin dato")', 'I3', 29),
(71, 'Avanzado 1', 'Errores', 'BUSCARX seguro', 'Usa SI.ERROR con BUSCARX para mostrar "No encontrado".', '=SI.ERROR(BUSCARX(H2,A2:A8,D2:D8),"No encontrado")', '=SI.ERROR(XLOOKUP(H2,A2:A8,D2:D8),"No encontrado")', 'I4', 29),
(72, 'Avanzado 1', 'BUSCARV', 'Dato extendido', 'Busca G3 en A2:E9 y devuelve la quinta columna.', '=BUSCARV(G3,A2:E9,5,FALSO)', '=BUSCARV(G3,A2:E9,5,0)', 'G3', 29),
(73, 'Avanzado 1', 'BUSCARV', 'Lookup corto', 'Busca F3 en A2:C9 y devuelve la segunda columna.', '=BUSCARV(F3,A2:C9,2,FALSO)', '=BUSCARV(F3,A2:C9,2,0)', 'G4', 30),
(74, 'Avanzado 1', 'BUSCARX', 'Lookup alterno', 'Usa BUSCARX para encontrar I2 en A2:A10 y devolver B2:B10.', '=BUSCARX(I2,A2:A10,B2:B10)', '=XLOOKUP(I2,A2:A10,B2:B10)', 'I2', 30),
(75, 'Avanzado 1', 'Errores', 'BUSCARV con mensaje', 'Controla BUSCARV para que, si falla, muestre "Revisar codigo".', '=SI.ERROR(BUSCARV(H5,A2:D10,3,FALSO),"Revisar codigo")', '=SI.ERROR(BUSCARV(H5,A2:D10,3,0),"Revisar codigo")', 'I5', 30),
(76, 'Avanzado 1', 'BUSCARV', 'Sexta columna', 'Busca G4 en A2:F9 y devuelve la sexta columna.', '=BUSCARV(G4,A2:F9,6,FALSO)', '=BUSCARV(G4,A2:F9,6,0)', 'G5', 31),
(77, 'Avanzado 1', 'BUSCARX', 'Cruce de columnas', 'Usa BUSCARX con H3 para buscar en C2:C8 y devolver F2:F8.', '=BUSCARX(H3,C2:C8,F2:F8)', '=XLOOKUP(H3,C2:C8,F2:F8)', 'I3', 31),
(78, 'Avanzado 1', 'Errores', 'BUSCARX protegido', 'Si BUSCARX falla, muestra "#N/A controlado".', '=SI.ERROR(BUSCARX(G2,A2:A7,C2:C7),"#N/A controlado")', '=SI.ERROR(XLOOKUP(G2,A2:A7,C2:C7),"#N/A controlado")', 'G2', 31),
(79, 'Avanzado 1', 'BUSCARV', 'Lookup desplazado', 'Busca F4 en B2:E8 y devuelve la cuarta columna.', '=BUSCARV(F4,B2:E8,4,FALSO)', '=BUSCARV(F4,B2:E8,4,0)', 'G4', 32),
(80, 'Avanzado 1', 'Errores', 'Sin coincidencia', 'Usa SI.ERROR con BUSCARV para mostrar "Sin coincidencia" cuando no encuentre datos.', '=SI.ERROR(BUSCARV(H2,A2:C6,2,FALSO),"Sin coincidencia")', '=SI.ERROR(BUSCARV(H2,A2:C6,2,0),"Sin coincidencia")', 'I2', 32),

(81, 'Avanzado 2', 'Fórmulas combinadas', 'Promedio regional', 'Divide la suma de C2:C8 para la región "Norte" entre el conteo de C2:C8.', '=SUMAR.SI(A2:A8,"Norte",C2:C8)/CONTAR(C2:C8)', NULL, 'D2', 34),
(82, 'Avanzado 2', 'Anidación', 'Meta lograda', 'Si el promedio de B2:B6 es al menos 70, muestra "Meta lograda"; si no, "Seguir".', '=SI(PROMEDIO(B2:B6)>=70,"Meta lograda","Seguir")', NULL, 'F2', 34),
(83, 'Avanzado 2', 'Caso real', 'Margen base', 'Resta la suma de C2:C6 a la suma de B2:B6.', '=SUMA(B2:B6)-SUMA(C2:C6)', NULL, 'F3', 34),
(84, 'Avanzado 2', 'Anidación', 'Escalamiento', 'Si el valor máximo de D2:D7 supera 1000, muestra "Escalar"; si no, "Normal".', '=SI(MAX(D2:D7)>1000,"Escalar","Normal")', NULL, 'F4', 35),
(85, 'Avanzado 2', 'Caso real', 'Activos promedio', 'Promedia C2:C8 solo cuando A2:A8 sea "Activo".', '=PROMEDIO.SI(A2:A8,"Activo",C2:C8)', NULL, 'C8', 35),
(86, 'Avanzado 2', 'Errores avanzados', 'Búsqueda segura avanzada', 'Si BUSCARV falla, devuelve "Sin dato".', '=SI.ERROR(BUSCARV(H2,A2:E8,5,FALSO),"Sin dato")', '=SI.ERROR(BUSCARV(H2,A2:E8,5,0),"Sin dato")', 'I2', 35),
(87, 'Avanzado 2', 'Fórmulas combinadas', 'Horas por dos cursos', 'Suma las horas del "Curso 1" y del "Curso 2" con una sola fórmula.', '=SUMAR.SI(B2:B9,"Curso 1",C2:C9)+SUMAR.SI(B2:B9,"Curso 2",C2:C9)', NULL, 'C9', 36),
(88, 'Avanzado 2', 'Anidación', 'Umbral mínimo', 'Si el valor mínimo de C2:C7 es menor a 50, muestra "Revisar"; si no, "Correcto".', '=SI(MIN(C2:C7)<50,"Revisar","Correcto")', NULL, 'F5', 36),
(89, 'Avanzado 2', 'Fórmulas combinadas', 'Escala ponderada', 'Multiplica el promedio de B2:B5 por el valor máximo de C2:C5.', '=PROMEDIO(B2:B5)*MAX(C2:C5)', NULL, 'F6', 36),
(90, 'Avanzado 2', 'Caso real', 'Tamaño de muestra', 'Si CONTAR(D2:D10) es mayor o igual a 5 muestra "Muestra suficiente"; si no "Insuficiente".', '=SI(CONTAR(D2:D10)>=5,"Muestra suficiente","Insuficiente")', NULL, 'F7', 37),
(91, 'Avanzado 2', 'Errores avanzados', 'Responsable pendiente', 'Usa SI.ERROR con BUSCARX para devolver "Pendiente" si no hay coincidencia.', '=SI.ERROR(BUSCARX(H2,A2:A8,E2:E8),"Pendiente")', '=SI.ERROR(XLOOKUP(H2,A2:A8,E2:E8),"Pendiente")', 'I3', 37),
(92, 'Avanzado 2', 'Fórmulas combinadas', 'Suma de dos filas', 'Suma B2:D2 y luego B3:D3 con una sola fórmula.', '=SUMA(B2:D2)+SUMA(B3:D3)', NULL, 'F8', 37),
(93, 'Avanzado 2', 'Caso real', 'Objetivo operativo', 'Si la suma condicionada de A2:A8 igual a "Si" en B2:B8 es al menos 100, muestra "Objetivo"; si no "Falta".', '=SI(SUMAR.SI(A2:A8,"Si",B2:B8)>=100,"Objetivo","Falta")', NULL, 'B8', 38),
(94, 'Avanzado 2', 'Fórmulas combinadas', 'Promedio selectivo', 'Promedia D2:D9 cuando C2:C9 sea mayor o igual a 80.', '=PROMEDIO.SI(C2:C9,">=80",D2:D9)', NULL, 'D9', 38),
(95, 'Avanzado 2', 'Errores avanzados', 'Actualizar tabla', 'Si BUSCARV falla, devuelve "Actualizar tabla".', '=SI.ERROR(BUSCARV(H3,A2:F8,6,FALSO),"Actualizar tabla")', '=SI.ERROR(BUSCARV(H3,A2:F8,6,0),"Actualizar tabla")', 'I4', 38),
(96, 'Avanzado 2', 'Optimización', 'Relación optimizada', 'Divide la suma de B2:B6 entre el máximo de C2:C6.', '=SUMA(B2:B6)/MAX(C2:C6)', NULL, 'F9', 39),
(97, 'Avanzado 2', 'Caso real', 'Equipo top', 'Si el PROMEDIO.SI del área "Ventas" en B2:B8 supera 500, muestra "Equipo top"; si no "En progreso".', '=SI(PROMEDIO.SI(A2:A8,"Ventas",B2:B8)>500,"Equipo top","En progreso")', NULL, 'C8', 39),
(98, 'Avanzado 2', 'Caso real', 'Utilidad oeste', 'Resta los costos del Oeste a las ventas del Oeste usando SUMAR.SI.', '=SUMAR.SI(A2:A10,"Oeste",C2:C10)-SUMAR.SI(A2:A10,"Oeste",D2:D10)', NULL, 'D10', 39),
(99, 'Avanzado 2', 'Errores avanzados', 'Responsable alterno', 'Usa SI.ERROR con BUSCARX para mostrar "Sin responsable" si no encuentra coincidencia.', '=SI.ERROR(BUSCARX(I2,B2:B9,F2:F9),"Sin responsable")', '=SI.ERROR(XLOOKUP(I2,B2:B9,F2:F9),"Sin responsable")', 'I2', 40),
(100, 'Avanzado 2', 'Caso real', 'Diagnóstico final', 'Si la suma de B2:B8 es mayor que la suma de C2:C8, muestra "Rentable"; si no "Optimizar".', '=SI(SUMA(B2:B8)>SUMA(C2:C8),"Rentable","Optimizar")', NULL, 'F10', 45);
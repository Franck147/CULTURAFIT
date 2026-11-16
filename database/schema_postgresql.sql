-- ========================================================
-- CULTURA FIT - ESQUEMA DE BASE DE DATOS POSTGRESQL (PRODUCCIÓN)
-- 100% Compatible con PostgreSQL 13+ / 14+ / 15+ / 16+
-- ========================================================

-- Crear base de datos (ejecutar por separado si es necesario):
-- CREATE DATABASE cultura_fit WITH ENCODING 'UTF8';
-- \c cultura_fit;

-- Desactivar temporalmente restricciones si es necesario
SET client_min_messages = warning;

-- 1. Tabla users (Usuarios del Sistema)
DROP TABLE IF EXISTS detalle_ventas CASCADE;
DROP TABLE IF EXISTS ventas CASCADE;
DROP TABLE IF EXISTS gastos CASCADE;
DROP TABLE IF EXISTS cajas CASCADE;
DROP TABLE IF EXISTS productos CASCADE;
DROP TABLE IF EXISTS categorias CASCADE;
DROP TABLE IF EXISTS medidas CASCADE;
DROP TABLE IF EXISTS rutinas CASCADE;
DROP TABLE IF EXISTS socios CASCADE;
DROP TABLE IF EXISTS configuracion CASCADE;
DROP TABLE IF EXISTS users CASCADE;

CREATE TABLE users (
    id BIGSERIAL PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    rol VARCHAR(30) DEFAULT 'cajero' CHECK (rol IN ('admin', 'cajero')),
    estado VARCHAR(20) DEFAULT 'activo' CHECK (estado IN ('activo', 'inactivo')),
    remember_token VARCHAR(100),
    created_at TIMESTAMP WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

-- Usuarios Iniciales (Password: admin123 / cajero123)
INSERT INTO users (nombre, email, password, rol, estado) VALUES
('Administrador Piero', 'admin@gym.com', '$2y$10$eeUFYFldSH9vWP3kElE3buIPXvXdEfIryMpYh9J47a6mxsju0q1GC', 'admin', 'activo'),
('Cajero Principal', 'cajero@gym.com', '$2y$10$1cHBZUuhxxHZAb7u9.9OeuAkW0V99JOTLWgisEaUaLQpIv98pWBj6', 'cajero', 'activo');

-- 2. Tabla configuracion
CREATE TABLE configuracion (
    id SERIAL PRIMARY KEY,
    nombre_sistema VARCHAR(100) DEFAULT 'CULTURA FIT',
    ruc VARCHAR(20) DEFAULT '20600000001',
    direccion VARCHAR(255) DEFAULT 'Av. Principal 123, Lima',
    telefono VARCHAR(20) DEFAULT '987654321',
    email VARCHAR(100) DEFAULT 'admin@gym.com',
    logo VARCHAR(255) DEFAULT 'logo_empresa.png',
    moneda VARCHAR(10) DEFAULT 'S/',
    created_at TIMESTAMP WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO configuracion (id, nombre_sistema, ruc, direccion, telefono, email, logo, moneda) VALUES
(1, 'CULTURA FIT', '20600000001', 'Av. Principal 123, Lima', '987654321', 'admin@gym.com', 'logo_empresa.png', 'S/');

-- 3. Tabla cajas
CREATE TABLE cajas (
    id BIGSERIAL PRIMARY KEY,
    usuario_id BIGINT NOT NULL REFERENCES users(id) ON DELETE RESTRICT,
    monto_inicial NUMERIC(10,2) NOT NULL DEFAULT 0.00,
    monto_final NUMERIC(10,2) DEFAULT 0.00,
    total_ventas NUMERIC(10,2) DEFAULT 0.00,
    total_gastos NUMERIC(10,2) DEFAULT 0.00,
    diferencia NUMERIC(10,2) DEFAULT 0.00,
    fecha_apertura TIMESTAMP WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    fecha_cierre TIMESTAMP WITHOUT TIME ZONE NULL,
    estado VARCHAR(20) DEFAULT 'abierta' CHECK (estado IN ('abierta', 'cerrada')),
    created_at TIMESTAMP WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_cajas_usuario ON cajas(usuario_id);
CREATE INDEX idx_cajas_estado ON cajas(estado);

-- 4. Tabla categorias
CREATE TABLE categorias (
    id BIGSERIAL PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    estado VARCHAR(20) DEFAULT 'activo' CHECK (estado IN ('activo', 'inactivo')),
    created_at TIMESTAMP WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO categorias (nombre, estado) VALUES
('Bebidas e Hidratación', 'activo'),
('Suplementación y Proteínas', 'activo'),
('Accesorios Deportivos', 'activo');

-- 5. Tabla productos
CREATE TABLE productos (
    id BIGSERIAL PRIMARY KEY,
    categoria_id BIGINT NOT NULL REFERENCES categorias(id) ON DELETE RESTRICT,
    codigo VARCHAR(50),
    nombre VARCHAR(150) NOT NULL,
    precio_compra NUMERIC(10,2) NOT NULL DEFAULT 0.00,
    precio_venta NUMERIC(10,2) NOT NULL DEFAULT 0.00,
    stock INT NOT NULL DEFAULT 0 CHECK (stock >= 0),
    foto VARCHAR(255),
    estado VARCHAR(20) DEFAULT 'activo' CHECK (estado IN ('activo', 'inactivo')),
    created_at TIMESTAMP WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_productos_categoria ON productos(categoria_id);
CREATE INDEX idx_productos_codigo ON productos(codigo);

INSERT INTO productos (id, categoria_id, codigo, nombre, precio_compra, precio_venta, stock, foto, estado) VALUES
(1, 6, NULL, 'Creatina Level 250 gr', 37.00, 65.00, 4, 'prod_1789151162_339.webp', 'activo'),
(2, 6, NULL, 'Creatina Level 500g', 72.00, 110.00, 2, 'prod_1786924669_220.png', 'activo'),
(3, 16, NULL, 'Creatina UN 500 GR', 73.50, 110.00, 3, 'prod_1789950321_290.webp', 'activo'),
(4, 16, NULL, 'Creatina UN 250 gr', 37.00, 65.00, 4, 'prod_1789151973_503.jpg', 'activo'),
(5, 11, NULL, 'Creatina Insane Labs 1 kg', 155.00, 180.00, 2, 'prod_1789150901_403.webp', 'activo'),
(6, 5, NULL, 'Creatina Ronie Coleman 1 kg', 140.00, 180.00, 3, 'prod_1789151360_594.webp', 'activo'),
(7, 5, NULL, 'Creatina Ronnie Coleman 300 gr', 54.00, 80.00, 6, 'prod_1789151390_116.jpg', 'activo'),
(8, 14, NULL, 'Creatina Yava Labs 1 kg', 112.00, 150.00, 3, 'prod_1789950353_325.jpg', 'activo'),
(9, 1, NULL, 'Creatina Dragon Pharma 1KG', 140.00, 200.00, 2, 'prod_1789150795_371.webp', 'activo'),
(10, 1, NULL, 'Creatina Dragon Pharma 300 Gr', 50.00, 85.00, 9, 'prod_1789150824_137.webp', 'activo'),
(11, 7, NULL, 'Creatina Dymatize 300 Gr', 75.00, 130.00, 3, 'prod_1789150871_311.webp', 'activo'),
(12, 12, NULL, 'Creatina Kevin Levrone 300 Gr', 40.00, 70.00, 6, 'prod_1789150982_823.webp', 'activo'),
(13, 12, NULL, 'Creatina Kevin Levrone 500 Gr', 71.00, 100.00, 4, 'prod_1789151122_983.jpg', 'activo'),
(14, 2, NULL, 'Creatina Ultimate 120 Gr', 15.00, 40.00, 6, 'prod_1789151539_700.webp', 'activo'),
(15, 2, NULL, 'Creatina Ultimate 300 Gr', 55.00, 90.00, 6, 'prod_1789151840_771.webp', 'activo'),
(16, 2, NULL, 'Creatina Ultimate Fruit Punch 300 Gr', 64.00, 100.00, 6, 'prod_1789151942_433.png', 'activo'),
(17, 2, NULL, 'Creatina Ultimate 1Kg', 140.00, 200.00, 2, 'prod_1789151764_751.webp', 'activo'),
(18, 2, NULL, 'Creatina Ultimate 1Kg', 140.00, 200.00, 2, 'prod_1789151778_492.webp', 'activo'),
(19, 8, NULL, 'Creatina Applied 150 Gr', 20.00, 40.00, 1, 'prod_1789150179_923.jpg', 'activo'),
(20, 8, NULL, 'Creatina Applied 250 Gr', 47.00, 80.00, 6, 'prod_1789150588_126.webp', 'activo'),
(21, 8, NULL, 'Creatina Applied 500 Gr', 78.00, 120.00, 4, 'prod_1789150715_369.webp', 'activo'),
(22, 8, NULL, 'Creatina Applied 1Kg', 140.00, 180.00, 2, 'prod_1789150474_695.webp', 'activo'),
(23, 3, NULL, 'Creatina Optimus Nutrition 300 Gr', 90.00, 130.00, 8, 'prod_1789151290_498.webp', 'activo'),
(24, 3, NULL, 'Creatina Optimus Nutrition 600 Gr', 142.00, 200.00, 3, 'prod_1789151326_891.webp', 'activo'),
(25, 3, NULL, 'Creatina Optimus Nutrition 1,2 Kg', 245.00, 290.00, 2, 'prod_1789151244_815.webp', 'activo'),
(26, 3, NULL, 'Creatina Optimus Nutrition 1,2 Kg', 245.00, 290.00, 2, 'prod_1789151216_381.webp', 'activo'),
(27, 10, NULL, 'Creatina Creaone 500 Gr', 57.00, 90.00, 2, 'prod_1789150745_484.webp', 'activo'),
(28, 13, NULL, 'Creatina Scitec Nutrition 300 Gr', 50.00, 80.00, 2, 'prod_1789151484_806.png', 'activo'),
(29, 13, NULL, 'Creatina Scitec Nutrition 300 Gr', 50.00, 80.00, 2, 'prod_1789151465_679.png', 'activo'),
(30, 2, NULL, 'Lipo 6 Black', 80.00, 120.00, 3, 'prod_1789950691_327.webp', 'activo'),
(31, 2, NULL, 'Lipo 6 Intense', 80.00, 130.00, 2, 'prod_1789950717_707.webp', 'activo'),
(32, 1, NULL, 'Black Viper', 100.00, 150.00, 2, 'prod_1789150240_454.webp', 'activo'),
(33, 12, NULL, 'Gold Levro Lean', 40.00, 80.00, 2, 'prod_1789950493_478.webp', 'activo'),
(34, 1, NULL, 'Fat Loss', 40.00, 80.00, 1, NULL, 'activo'),
(35, 1, NULL, 'Fat Loss', 40.00, 80.00, 1, NULL, 'activo'),
(36, 6, NULL, 'Cla', 50.00, 80.00, 3, 'prod_1789150440_288.webp', 'activo'),
(37, 6, NULL, 'Cla', 50.00, 80.00, 3, 'prod_1789150433_234.webp', 'activo'),
(38, 2, NULL, 'L-carnitine 3000', 65.00, 120.00, 3, 'prod_1789950603_850.webp', 'activo'),
(39, 2, NULL, 'L-carnitine 3000', 65.00, 120.00, 3, 'prod_1789950634_403.webp', 'activo'),
(40, 2, NULL, 'L-carnitine pastillas1000', 60.00, 100.00, 4, 'prod_1789950663_263.jpg', 'activo'),
(41, 11, NULL, 'Psychotic red 30 serv', 100.00, 150.00, 2, 'prod_1789951150_500.webp', 'activo'),
(42, 11, NULL, 'Psychotic Saw', 140.00, 180.00, 2, 'prod_1789951193_652.webp', 'activo'),
(43, 11, NULL, 'Psychotic Test', 140.00, 180.00, 2, 'prod_1789951221_267.jpg', 'activo'),
(44, 11, NULL, 'Psychotic  Black', 110.00, 140.00, 1, 'prod_1789951164_169.webp', 'activo'),
(45, 16, NULL, 'Psychosis', 100.00, 140.00, 3, 'prod_1789951091_422.webp', 'activo'),
(46, 3, NULL, 'Psychosis', 100.00, 140.00, 3, 'prod_1789951100_923.webp', 'activo'),
(47, 16, NULL, 'Hyde', 100.00, 140.00, 1, 'prod_1789950540_157.jpg', 'activo'),
(48, 6, NULL, 'Pre boost', 72.00, 120.00, 3, 'prod_1789950962_201.webp', 'activo'),
(49, 3, NULL, 'Nox rage', 67.00, 110.00, 3, 'prod_1789950771_375.webp', 'activo'),
(50, 3, NULL, 'Agresive Pump', 70.00, 110.00, 2, 'prod_1789150231_793.jpg', 'activo'),
(51, 16, NULL, 'Joker', 125.00, 180.00, 2, 'prod_1789950577_734.webp', 'activo'),
(52, 16, NULL, 'Maniac', 120.00, 170.00, 3, 'prod_1789950746_632.webp', 'activo'),
(53, 16, NULL, 'C4 Sport 30 Serv', 107.00, 150.00, 1, 'prod_1789150370_628.webp', 'activo'),
(54, 16, NULL, 'C4 Original 60 Serv', 150.00, 190.00, 0, 'prod_1789150327_860.jpeg', 'activo'),
(55, 16, NULL, 'Out Rage', 115.00, 160.00, 2, 'prod_1789950860_310.jpg', 'activo'),
(56, 4, NULL, 'BigM 2kg', 0.00, 85.00, 2, 'prod_1789953175_527.jpg', 'activo'),
(57, 4, NULL, 'BigM 3kg', 0.00, 110.00, 3, 'prod_1789953243_224.png', 'activo'),
(58, 4, NULL, 'BigM 5kg', 0.00, 150.00, 3, 'prod_1789953322_562.png', 'activo'),
(59, 4, NULL, 'Soja Complex 1k', 0.00, 100.00, 3, 'prod_1789953467_181.png', 'activo'),
(60, 4, NULL, 'Iso Whey 90 1.1 Kg', 0.00, 120.00, 3, 'prod_1789953610_434.webp', 'activo'),
(61, 4, NULL, 'Iso Whey 90 2.5 kg', 0.00, 250.00, 3, 'prod_1789953718_949.webp', 'activo'),
(62, 4, NULL, 'Iso Whey 90 5kg', 0.00, 380.00, 3, 'prod_1789953839_647.webp', 'activo'),
(63, 4, NULL, 'Nitro Whey 5kg', 0.00, 280.00, 3, 'prod_1789954004_681.webp', 'activo'),
(64, 4, NULL, 'Nitro Whey 2.5 kg', 0.00, 180.00, 3, 'prod_1789954040_598.webp', 'activo'),
(65, 4, NULL, 'Nitro Whey 1kg', 0.00, 100.00, 3, 'prod_1789954125_812.webp', 'activo'),
(66, 4, NULL, 'Whey Pro 3 kg', 0.00, 160.00, 3, 'prod_1789954337_144.webp', 'activo'),
(67, 4, NULL, 'Whey Pro 5 kg', 0.00, 100.00, 3, 'prod_1789954363_209.webp', 'activo'),
(68, 4, NULL, 'Whey Pro 1.1 kg', 0.00, 90.00, 3, 'prod_1789954440_984.webp', 'activo'),
(69, 4, NULL, 'FEM PRO 1.1 KG', 0.00, 100.00, 3, 'prod_1789954775_358.jpg', 'activo'),
(70, 4, NULL, 'Fitfem Iso Fit 1.1 Kg', 0.00, 100.00, 3, 'prod_1789954852_744.webp', 'activo'),
(71, 4, NULL, 'Carbo Plex  5kg', 0.00, 110.00, 1, 'prod_1790005185_474.png', 'activo'),
(72, 4, NULL, 'Carbo Plex  2kg', 0.00, 70.00, 1, 'prod_1790005299_504.png', 'activo'),
(73, 4, NULL, 'Maltodex 5kg', 0.00, 100.00, 1, 'prod_1790005492_171.png', 'activo'),
(74, 4, NULL, 'Pro Mass XL 2kg', 0.00, 90.00, 2, 'prod_1790005737_562.png', 'activo'),
(75, 4, NULL, 'Pro Mass XL   3kg', 0.00, 110.00, 1, 'prod_1790006591_644.png', 'activo'),
(76, 4, NULL, 'Lady Gain', 0.00, 100.00, 3, 'prod_1790446507_630.jpg', 'activo'),
(77, 4, NULL, 'Lady pro', 0.00, 100.00, 3, 'prod_1790446585_496.webp', 'activo'),
(78, 4, NULL, 'Testo Zma pro', 0.00, 120.00, 3, 'prod_1790446768_135.webp', 'activo'),
(79, 4, NULL, 'one whey isolate', 0.00, 120.00, 3, 'prod_1790446927_329.webp', 'activo'),
(80, 4, NULL, 'one whey pro', 0.00, 100.00, 3, 'prod_1790446990_489.webp', 'activo'),
(81, 4, NULL, 'whey powder', 0.00, 90.00, 3, 'prod_1790447094_220.jpg', 'activo'),
(82, 4, NULL, 'One Mass', 0.00, 100.00, 3, 'prod_1790447337_422.webp', 'activo'),
(83, 4, NULL, 'Collagen one', 0.00, 100.00, 3, 'prod_1790447432_557.jpg', 'activo'),
(84, 4, NULL, 'Bcaa 1 kg', 0.00, 120.00, 3, 'prod_1790447531_226.webp', 'activo'),
(85, 4, NULL, 'Bcaa 500 gr 85.00', 0.00, 85.00, 1, 'prod_1790447644_774.jpg', 'activo'),
(86, 4, NULL, 'One pre 500 gr', 0.00, 85.00, 3, 'prod_1790447701_983.jpg', 'activo'),
(87, 4, NULL, 'One pre 1 kg', 0.00, 110.00, 3, 'prod_1790447749_564.webp', 'activo'),
(88, 4, NULL, 'Pro Mass XL 5kg', 0.00, 200.00, 3, 'prod_1790447802_741.png', 'activo'),
(89, 4, NULL, '100%Whey 1kg', 0.00, 100.00, 3, 'prod_1790447860_795.webp', 'activo'),
(90, 4, NULL, '100 %whey 3 kg', 0.00, 160.00, 3, 'prod_1790447905_493.webp', 'activo'),
(91, 4, NULL, 'Iso pro 1 kg', 0.00, 110.00, 3, 'prod_1790447949_325.webp', 'activo'),
(92, 4, NULL, 'Iso Pro 3 kg', 0.00, 230.00, 3, 'prod_1790447991_420.webp', 'activo'),
(93, 4, NULL, 'Hydro 2.5 lb', 0.00, 220.00, 3, 'prod_1790448299_878.jpg', 'activo'),
(94, 4, NULL, 'Hydro 5.5 lb', 0.00, 320.00, 3, 'prod_1790448355_586.webp', 'activo'),
(95, 4, NULL, 'Supreme mass', 0.00, 180.00, 3, 'prod_1790448515_878.webp', 'activo'),
(96, 17, NULL, 'BATIDO PROTEICO', 10.00, 13.00, 8, NULL, 'activo'),
(97, 18, NULL, 'PRO STAR WEY 5LB', 120.00, 150.00, 20, NULL, 'activo'),
(98, 18, NULL, 'PRO STAR WEY 5LB', 120.00, 150.00, 20, NULL, 'activo');

-- 6. Tabla socios (Clientes)
CREATE TABLE socios (
    id BIGSERIAL PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    dni VARCHAR(20) UNIQUE NOT NULL,
    email VARCHAR(100),
    telefono VARCHAR(20),
    whatsapp_api_key VARCHAR(50),
    fecha_registro TIMESTAMP WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    estado VARCHAR(20) DEFAULT 'activo' CHECK (estado IN ('activo', 'inactivo', 'pendiente')),
    foto VARCHAR(255),
    created_at TIMESTAMP WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_socios_dni ON socios(dni);
CREATE INDEX idx_socios_estado ON socios(estado);

INSERT INTO socios (id, nombre, dni, email, telefono, estado, foto) VALUES
(1, 'Franck Adler Bautista Cisneros', '74985447', 'adlercisneros147@gmail.com', '982424917', 'activo', NULL),
(2, 'yandi cano', '12345678', 'yandi@culturafit.com', '987654321', 'activo', NULL);

-- 7. Tabla medidas (Progreso Físico)
CREATE TABLE medidas (
    id BIGSERIAL PRIMARY KEY,
    socio_id BIGINT NOT NULL REFERENCES socios(id) ON DELETE CASCADE,
    peso NUMERIC(5,2),
    grasa NUMERIC(5,2),
    cintura NUMERIC(5,2),
    brazo NUMERIC(5,2),
    fecha DATE NOT NULL,
    created_at TIMESTAMP WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_medidas_socio ON medidas(socio_id);
CREATE INDEX idx_medidas_fecha ON medidas(fecha);

INSERT INTO medidas (socio_id, peso, grasa, cintura, brazo, fecha) VALUES
(1, 82.50, 22.40, 92.00, 34.50, CURRENT_DATE - INTERVAL '60 days'),
(1, 80.10, 20.10, 89.00, 35.20, CURRENT_DATE - INTERVAL '30 days'),
(1, 78.40, 18.50, 86.50, 36.00, CURRENT_DATE);

-- 8. Tabla rutinas de entrenamiento
CREATE TABLE rutinas (
    id BIGSERIAL PRIMARY KEY,
    socio_id BIGINT NOT NULL REFERENCES socios(id) ON DELETE CASCADE,
    dia1 TEXT,
    dia2 TEXT,
    dia3 TEXT,
    dia4 TEXT,
    dia5 TEXT,
    dia6 TEXT,
    observaciones TEXT,
    fecha_asignacion TIMESTAMP WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    created_at TIMESTAMP WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_rutinas_socio ON rutinas(socio_id);

INSERT INTO rutinas (socio_id, dia1, dia2, dia3, dia4, dia5, dia6, observaciones) VALUES
(1, 'Pecho y Tríceps: Press Plano 4x10, Press Inclinado 4x12, Fondos 3x12', 
    'Espalda y Bíceps: Jalón al Pecho 4x12, Remo con Barra 4x10, Curl Barra 3x12', 
    'Pierna Completa: Sentadilla Libre 4x8, Prensa 4x12, Peso Muerto Rumano 4x10', 
    'Hombro y Trapecio: Press Militar 4x10, Elevaciones Laterales 4x15, Encogimientos 4x12', 
    'Core y Cardio Hiit: 25 min cinta intervalos + Plancha 4x1 min', 
    'Descanso Activo / Estiramientos', 
    'Mantener hidratación constante y enfocar en técnica controlada.');

-- 9. Tabla ventas (POS)
CREATE TABLE ventas (
    id BIGSERIAL PRIMARY KEY,
    caja_id BIGINT NOT NULL REFERENCES cajas(id) ON DELETE RESTRICT,
    socio_id BIGINT NULL REFERENCES socios(id) ON DELETE SET NULL,
    total NUMERIC(10,2) NOT NULL DEFAULT 0.00,
    descuento NUMERIC(10,2) NOT NULL DEFAULT 0.00,
    metodo_pago VARCHAR(20) DEFAULT 'efectivo' CHECK (metodo_pago IN ('efectivo', 'tarjeta', 'transferencia', 'yape_plin', 'yape', 'plin')),
    fecha TIMESTAMP WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    created_at TIMESTAMP WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_ventas_caja ON ventas(caja_id);
CREATE INDEX idx_ventas_socio ON ventas(socio_id);
CREATE INDEX idx_ventas_fecha ON ventas(fecha);

-- 10. Tabla detalle_ventas
CREATE TABLE detalle_ventas (
    id BIGSERIAL PRIMARY KEY,
    venta_id BIGINT NOT NULL REFERENCES ventas(id) ON DELETE CASCADE,
    producto_id BIGINT NOT NULL REFERENCES productos(id) ON DELETE RESTRICT,
    cantidad INT NOT NULL CHECK (cantidad > 0),
    precio_unitario NUMERIC(10,2) NOT NULL,
    subtotal NUMERIC(10,2) NOT NULL,
    created_at TIMESTAMP WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_detalle_venta ON detalle_ventas(venta_id);
CREATE INDEX idx_detalle_producto ON detalle_ventas(producto_id);

-- 11. Tabla gastos
CREATE TABLE gastos (
    id BIGSERIAL PRIMARY KEY,
    caja_id BIGINT NULL REFERENCES cajas(id) ON DELETE SET NULL,
    usuario_id BIGINT NULL REFERENCES users(id) ON DELETE SET NULL,
    descripcion VARCHAR(255) NOT NULL,
    monto NUMERIC(10,2) NOT NULL CHECK (monto > 0),
    fecha DATE NOT NULL,
    estado VARCHAR(20) DEFAULT 'creado' CHECK (estado IN ('creado', 'anulado')),
    motivo_anulacion VARCHAR(255),
    created_at TIMESTAMP WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_gastos_caja ON gastos(caja_id);
CREATE INDEX idx_gastos_fecha ON gastos(fecha);
CREATE INDEX idx_gastos_estado ON gastos(estado);


-- Sincronizar secuencias de PostgreSQL con los IDs importados
SELECT setval('categorias_id_seq', (SELECT MAX(id) FROM categorias));
SELECT setval('productos_id_seq', (SELECT MAX(id) FROM productos));
SELECT setval('socios_id_seq', (SELECT MAX(id) FROM socios));

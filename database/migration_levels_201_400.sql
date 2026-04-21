-- Migración: Agregar niveles 201 a 400 (preguntas para duelos)
-- Ejecutar DESPUÉS de migration_levels_101_200.sql

INSERT INTO levels (numero, dificultad, categoria, titulo, consigna, respuesta_correcta, respuestas_alternativas, formula_target, points_reward) VALUES

-- ══════════════════════════════════════════════════════
-- SUMAR.SI.CONJUNTO (201-215)
-- ══════════════════════════════════════════════════════

(201, 'Experto 2', 'SUMAR.SI.CONJUNTO', 'Suma con dos criterios', 'Suma los valores de C2:C10 donde A2:A10 sea "Norte" y B2:B10 sea "Laptop" en F2.', '=SUMAR.SI.CONJUNTO(C2:C10,A2:A10,"Norte",B2:B10,"Laptop")', '=SUMIFS(C2:C10,A2:A10,"Norte",B2:B10,"Laptop")', 'F2', 60),
(202, 'Experto 2', 'SUMAR.SI.CONJUNTO', 'Ventas activas al norte', 'Suma D2:D10 donde A2:A10 sea "Norte" y E2:E10 sea "Activo" en G2.', '=SUMAR.SI.CONJUNTO(D2:D10,A2:A10,"Norte",E2:E10,"Activo")', '=SUMIFS(D2:D10,A2:A10,"Norte",E2:E10,"Activo")', 'G2', 60),
(203, 'Experto 2', 'SUMAR.SI.CONJUNTO', 'Total por región y categoría', 'Suma B2:B10 donde A2:A10 sea "Sur" y C2:C10 sea "Electrónica" en F3.', '=SUMAR.SI.CONJUNTO(B2:B10,A2:A10,"Sur",C2:C10,"Electrónica")', '=SUMIFS(B2:B10,A2:A10,"Sur",C2:C10,"Electrónica")', 'F3', 60),
(204, 'Experto 2', 'SUMAR.SI.CONJUNTO', 'Suma mayor a umbral', 'Suma C2:C9 donde B2:B9 sea mayor que 100 y D2:D9 sea "Activo" en F4.', '=SUMAR.SI.CONJUNTO(C2:C9,B2:B9,">100",D2:D9,"Activo")', '=SUMIFS(C2:C9,B2:B9,">100",D2:D9,"Activo")', 'F4', 61),
(205, 'Experto 2', 'SUMAR.SI.CONJUNTO', 'Comisión vendedor activo', 'Suma E2:E10 donde A2:A10 sea "Ventas" y D2:D10 sea mayor o igual a 500 en G3.', '=SUMAR.SI.CONJUNTO(E2:E10,A2:A10,"Ventas",D2:D10,">=500")', '=SUMIFS(E2:E10,A2:A10,"Ventas",D2:D10,">=500")', 'G3', 61),
(206, 'Experto 2', 'SUMAR.SI.CONJUNTO', 'Tres criterios combinados', 'Suma C2:C10 donde A2:A10 sea "Norte", B2:B10 sea "Monitor" y E2:E10 sea "Activo" en G4.', '=SUMAR.SI.CONJUNTO(C2:C10,A2:A10,"Norte",B2:B10,"Monitor",E2:E10,"Activo")', '=SUMIFS(C2:C10,A2:A10,"Norte",B2:B10,"Monitor",E2:E10,"Activo")', 'G4', 62),
(207, 'Experto 2', 'SUMAR.SI.CONJUNTO', 'Ingresos menores al límite', 'Suma B2:B9 donde B2:B9 sea menor que 300 y C2:C9 sea mayor que 50 en F5.', '=SUMAR.SI.CONJUNTO(B2:B9,B2:B9,"<300",C2:C9,">50")', '=SUMIFS(B2:B9,B2:B9,"<300",C2:C9,">50")', 'F5', 62),
(208, 'Experto 2', 'SUMAR.SI.CONJUNTO', 'Suma por código y estado', 'Suma D2:D8 donde A2:A8 sea "P100" y E2:E8 sea "Aprobado" en F6.', '=SUMAR.SI.CONJUNTO(D2:D8,A2:A8,"P100",E2:E8,"Aprobado")', '=SUMIFS(D2:D8,A2:A8,"P100",E2:E8,"Aprobado")', 'F6', 62),
(209, 'Experto 2', 'SUMAR.SI.CONJUNTO', 'Ventas en rango de fechas', 'Suma C2:C10 donde B2:B10 sea mayor o igual a 100 y B2:B10 sea menor o igual a 500 en F7.', '=SUMAR.SI.CONJUNTO(C2:C10,B2:B10,">=100",B2:B10,"<=500")', '=SUMIFS(C2:C10,B2:B10,">=100",B2:B10,"<=500")', 'F7', 63),
(210, 'Experto 2', 'SUMAR.SI.CONJUNTO', 'Stock por categoría activa', 'Suma E2:E10 donde C2:C10 sea "Redes" y F2:F10 sea "Activo" en G5.', '=SUMAR.SI.CONJUNTO(E2:E10,C2:C10,"Redes",F2:F10,"Activo")', '=SUMIFS(E2:E10,C2:C10,"Redes",F2:F10,"Activo")', 'G5', 63),
(211, 'Experto 2', 'SUMAR.SI.CONJUNTO', 'Total zona sur premium', 'Suma D2:D9 donde A2:A9 sea "Sur" y C2:C9 sea "Premium" en G6.', '=SUMAR.SI.CONJUNTO(D2:D9,A2:A9,"Sur",C2:C9,"Premium")', '=SUMIFS(D2:D9,A2:A9,"Sur",C2:C9,"Premium")', 'G6', 63),
(212, 'Experto 2', 'SUMAR.SI.CONJUNTO', 'Suma diferente de cero', 'Suma B2:B10 donde B2:B10 sea diferente de 0 y A2:A10 sea "Norte" en F8.', '=SUMAR.SI.CONJUNTO(B2:B10,B2:B10,"<>0",A2:A10,"Norte")', '=SUMIFS(B2:B10,B2:B10,"<>0",A2:A10,"Norte")', 'F8', 64),
(213, 'Experto 2', 'SUMAR.SI.CONJUNTO', 'Horas extra por equipo', 'Suma C2:C8 donde A2:A8 sea "Soporte" y B2:B8 sea mayor que 8 en F9.', '=SUMAR.SI.CONJUNTO(C2:C8,A2:A8,"Soporte",B2:B8,">8")', '=SUMIFS(C2:C8,A2:A8,"Soporte",B2:B8,">8")', 'F9', 64),
(214, 'Experto 2', 'SUMAR.SI.CONJUNTO', 'Gasto por tipo aprobado', 'Suma E2:E10 donde D2:D10 sea "Aprobado" y C2:C10 sea "Gastos" en G7.', '=SUMAR.SI.CONJUNTO(E2:E10,D2:D10,"Aprobado",C2:C10,"Gastos")', '=SUMIFS(E2:E10,D2:D10,"Aprobado",C2:C10,"Gastos")', 'G7', 64),
(215, 'Experto 2', 'SUMAR.SI.CONJUNTO', 'Ventas con descuento', 'Suma B2:B10 donde A2:A10 sea "Oeste" y D2:D10 sea menor que 50 en G8.', '=SUMAR.SI.CONJUNTO(B2:B10,A2:A10,"Oeste",D2:D10,"<50")', '=SUMIFS(B2:B10,A2:A10,"Oeste",D2:D10,"<50")', 'G8', 65),

-- ══════════════════════════════════════════════════════
-- CONTAR.SI.CONJUNTO (216-225)
-- ══════════════════════════════════════════════════════

(216, 'Experto 2', 'CONTAR.SI.CONJUNTO', 'Contar dos condiciones', 'Cuenta cuántas filas de A2:A10 son "Norte" y B2:B10 son "Activo" en G2.', '=CONTAR.SI.CONJUNTO(A2:A10,"Norte",B2:B10,"Activo")', '=COUNTIFS(A2:A10,"Norte",B2:B10,"Activo")', 'G2', 65),
(217, 'Experto 2', 'CONTAR.SI.CONJUNTO', 'Productos en rango de precio', 'Cuenta cuántos valores de D2:D10 son mayores que 100 y menores que 500 en G3.', '=CONTAR.SI.CONJUNTO(D2:D10,">100",D2:D10,"<500")', '=COUNTIFS(D2:D10,">100",D2:D10,"<500")', 'G3', 65),
(218, 'Experto 2', 'CONTAR.SI.CONJUNTO', 'Empleados activos por zona', 'Cuenta cuántas filas de C2:C9 son "Ventas" y E2:E9 son "Activo" en G4.', '=CONTAR.SI.CONJUNTO(C2:C9,"Ventas",E2:E9,"Activo")', '=COUNTIFS(C2:C9,"Ventas",E2:E9,"Activo")', 'G4', 66),
(219, 'Experto 2', 'CONTAR.SI.CONJUNTO', 'Aprobados en dos materias', 'Cuenta cuántas filas tienen B2:B8 mayor o igual a 70 y C2:C8 mayor o igual a 70 en G5.', '=CONTAR.SI.CONJUNTO(B2:B8,">=70",C2:C8,">=70")', '=COUNTIFS(B2:B8,">=70",C2:C8,">=70")', 'G5', 66),
(220, 'Experto 2', 'CONTAR.SI.CONJUNTO', 'Pedidos pendientes al sur', 'Cuenta cuántas filas de A2:A10 son "Sur" y D2:D10 son "Pendiente" en G6.', '=CONTAR.SI.CONJUNTO(A2:A10,"Sur",D2:D10,"Pendiente")', '=COUNTIFS(A2:A10,"Sur",D2:D10,"Pendiente")', 'G6', 66),
(221, 'Experto 2', 'CONTAR.SI.CONJUNTO', 'Ítems dentro de stock', 'Cuenta cuántos valores de E2:E10 son mayores que 0 y menores o iguales a 20 en G7.', '=CONTAR.SI.CONJUNTO(E2:E10,">0",E2:E10,"<=20")', '=COUNTIFS(E2:E10,">0",E2:E10,"<=20")', 'G7', 67),
(222, 'Experto 2', 'CONTAR.SI.CONJUNTO', 'Tres criterios de conteo', 'Cuenta cuántas filas de A2:A10 son "Norte", B2:B10 son "Laptop" y E2:E10 son "Activo" en G8.', '=CONTAR.SI.CONJUNTO(A2:A10,"Norte",B2:B10,"Laptop",E2:E10,"Activo")', '=COUNTIFS(A2:A10,"Norte",B2:B10,"Laptop",E2:E10,"Activo")', 'G8', 67),
(223, 'Experto 2', 'CONTAR.SI.CONJUNTO', 'Clientes con deuda alta', 'Cuenta cuántas filas de C2:C9 son "Premium" y D2:D9 son mayores que 1000 en G9.', '=CONTAR.SI.CONJUNTO(C2:C9,"Premium",D2:D9,">1000")', '=COUNTIFS(C2:C9,"Premium",D2:D9,">1000")', 'G9', 67),
(224, 'Experto 2', 'CONTAR.SI.CONJUNTO', 'Stock bajo por categoría', 'Cuenta cuántas filas de C2:C10 son "Audio" y E2:E10 son menores que 10 en G10.', '=CONTAR.SI.CONJUNTO(C2:C10,"Audio",E2:E10,"<10")', '=COUNTIFS(C2:C10,"Audio",E2:E10,"<10")', 'G10', 68),
(225, 'Experto 2', 'CONTAR.SI.CONJUNTO', 'Registros doble criterio numérico', 'Cuenta cuántas filas de B2:B10 son mayores o iguales a 500 y C2:C10 son menores que 300 en G11.', '=CONTAR.SI.CONJUNTO(B2:B10,">=500",C2:C10,"<300")', '=COUNTIFS(B2:B10,">=500",C2:C10,"<300")', 'G11', 68),

-- ══════════════════════════════════════════════════════
-- FUNCIONES DE FECHA (226-245)
-- ══════════════════════════════════════════════════════

(226, 'Intermedio 2', 'HOY', 'Fecha de hoy', 'Inserta la fecha de hoy en la celda B2.', '=HOY()', '=TODAY()', 'B2', 40),
(227, 'Intermedio 2', 'AHORA', 'Fecha y hora actual', 'Inserta la fecha y hora actuales en la celda B3.', '=AHORA()', '=NOW()', 'B3', 40),
(228, 'Intermedio 2', 'AÑO', 'Extraer el año', 'Extrae el año de la fecha en A2 en la celda B2.', '=AÑO(A2)', '=YEAR(A2)', 'B2', 40),
(229, 'Intermedio 2', 'MES', 'Extraer el mes', 'Extrae el número de mes de la fecha en A3 en la celda B3.', '=MES(A3)', '=MONTH(A3)', 'B3', 41),
(230, 'Intermedio 2', 'DIA', 'Extraer el día', 'Extrae el número de día de la fecha en A4 en la celda B4.', '=DIA(A4)', '=DAY(A4)', 'B4', 41),
(231, 'Intermedio 2', 'FECHA', 'Construir fecha', 'Construye una fecha con el año de D2, mes de D3 y día de D4 en la celda E2.', '=FECHA(D2,D3,D4)', '=DATE(D2,D3,D4)', 'E2', 41),
(232, 'Intermedio 2', 'DIASEM', 'Día de la semana', 'Devuelve el número de día de la semana de A2 (donde 1=domingo) en B2.', '=DIASEM(A2,1)', '=WEEKDAY(A2,1)', 'B2', 42),
(233, 'Intermedio 2', 'DIAS', 'Días entre fechas', 'Calcula los días transcurridos entre A2 y B2 en C2.', '=DIAS(B2,A2)', '=DAYS(B2,A2)||=B2-A2', 'C2', 42),
(234, 'Intermedio 2', 'DIA.LAB', 'Día laborable siguiente', 'Calcula la fecha 10 días laborables después de A2 en B2.', '=DIA.LAB(A2,10)', '=WORKDAY(A2,10)', 'B2', 42),
(235, 'Intermedio 2', 'FIN.MES', 'Último día del mes', 'Devuelve el último día del mes de A2 (sin desplazamiento) en B2.', '=FIN.MES(A2,0)', '=EOMONTH(A2,0)', 'B2', 43),
(236, 'Intermedio 2', 'SIFECHA', 'Años de antigüedad', 'Calcula los años completos entre A2 y HOY() en B2.', '=SIFECHA(A2,HOY(),"Y")', '=DATEDIF(A2,TODAY(),"Y")', 'B2', 43),
(237, 'Intermedio 2', 'SIFECHA', 'Meses de servicio', 'Calcula los meses completos entre A3 y HOY() en B3.', '=SIFECHA(A3,HOY(),"M")', '=DATEDIF(A3,TODAY(),"M")', 'B3', 43),
(238, 'Intermedio 2', 'NUM.DE.SEMANA', 'Número de semana', 'Devuelve el número de semana del año de la fecha en A2 en B2.', '=NUM.DE.SEMANA(A2)', '=WEEKNUM(A2)', 'B2', 44),
(239, 'Intermedio 2', 'DIAS.LAB', 'Días laborables entre fechas', 'Cuenta los días laborables entre A2 y B2 en C2.', '=DIAS.LAB(A2,B2)', '=NETWORKDAYS(A2,B2)', 'C2', 44),
(240, 'Intermedio 2', 'FECHA.MES', 'Fecha de vencimiento', 'Calcula la fecha 3 meses después de A2 en B2.', '=FECHA.MES(A2,3)', '=EDATE(A2,3)', 'B2', 44),
(241, 'Intermedio 2', 'AÑO', 'Año de nacimiento en celda de referencia', 'Extrae el año de la fecha en C5 y coloca el resultado en D5.', '=AÑO(C5)', '=YEAR(C5)', 'D5', 45),
(242, 'Intermedio 2', 'HOY', 'Edad en años', 'Calcula la edad en años completos a partir de la fecha en A2 usando la fecha de hoy en B2.', '=SIFECHA(A2,HOY(),"Y")', '=DATEDIF(A2,TODAY(),"Y")', 'B2', 45),
(243, 'Intermedio 2', 'MES', 'Mes de la factura', 'Extrae el número de mes de la fecha en B3 en la celda C3.', '=MES(B3)', '=MONTH(B3)', 'C3', 45),
(244, 'Intermedio 2', 'FIN.MES', 'Fin del mes siguiente', 'Devuelve el último día del mes siguiente al de A2 en B2.', '=FIN.MES(A2,1)', '=EOMONTH(A2,1)', 'B2', 46),
(245, 'Intermedio 2', 'DIA.LAB', 'Fecha entrega laboral', 'Calcula la fecha 5 días laborables antes de A2 en B2.', '=DIA.LAB(A2,-5)', '=WORKDAY(A2,-5)', 'B2', 46),

-- ══════════════════════════════════════════════════════
-- MATEMÁTICAS AVANZADAS (246-260)
-- ══════════════════════════════════════════════════════

(246, 'Intermedio 1', 'ENTERO', 'Parte entera', 'Devuelve la parte entera del valor de A2 en B2.', '=ENTERO(A2)', '=INT(A2)', 'B2', 35),
(247, 'Intermedio 1', 'TRUNCAR', 'Truncar decimales', 'Trunca el valor de B2 a 2 decimales en C2.', '=TRUNCAR(B2,2)', '=TRUNC(B2,2)', 'C2', 35),
(248, 'Intermedio 1', 'RESIDUO', 'Resto de la división', 'Calcula el residuo de dividir A2 entre B2 en C2.', '=RESIDUO(A2,B2)', '=MOD(A2,B2)', 'C2', 36),
(249, 'Intermedio 1', 'POTENCIA', 'Elevar a potencia', 'Eleva B2 al cuadrado en C2.', '=POTENCIA(B2,2)', '=B2^2||=POWER(B2,2)', 'C2', 36),
(250, 'Intermedio 1', 'RAIZ', 'Raíz cuadrada', 'Calcula la raíz cuadrada de A3 en B3.', '=RAIZ(A3)', '=SQRT(A3)', 'B3', 36),
(251, 'Intermedio 1', 'SUMAPRODUCTO', 'Suma de productos', 'Multiplica cada valor de B2:B6 por C2:C6 y suma todos los resultados en D2.', '=SUMAPRODUCTO(B2:B6,C2:C6)', '=SUMPRODUCT(B2:B6,C2:C6)', 'D2', 37),
(252, 'Intermedio 1', 'SUMAPRODUCTO', 'Ingreso total ponderado', 'Calcula la suma de B2:B8 multiplicado por C2:C8 en D3.', '=SUMAPRODUCTO(B2:B8,C2:C8)', '=SUMPRODUCT(B2:B8,C2:C8)', 'D3', 37),
(253, 'Intermedio 1', 'REDONDEAR.MAS', 'Redondeo hacia arriba', 'Redondea A2 hacia arriba a 0 decimales en B2.', '=REDONDEAR.MAS(A2,0)', '=ROUNDUP(A2,0)', 'B2', 37),
(254, 'Intermedio 1', 'REDONDEAR.MENOS', 'Redondeo hacia abajo', 'Redondea A3 hacia abajo a 1 decimal en B3.', '=REDONDEAR.MENOS(A3,1)', '=ROUNDDOWN(A3,1)', 'B3', 38),
(255, 'Intermedio 1', 'RESIDUO', 'Par o impar', 'Si el residuo de dividir A2 entre 2 es 0, muestra "Par"; si no "Impar" en B2.', '=SI(RESIDUO(A2,2)=0,"Par","Impar")', '=SI(MOD(A2,2)=0,"Par","Impar")', 'B2', 38),
(256, 'Intermedio 1', 'POTENCIA', 'Cálculo de área', 'Calcula el área de un cuadrado con lado B2 (B2 al cuadrado) en C2.', '=POTENCIA(B2,2)', '=B2^2||=POWER(B2,2)', 'C2', 38),
(257, 'Intermedio 1', 'ENTERO', 'Horas completas', 'Devuelve la parte entera de dividir A2 entre 60 en B2.', '=ENTERO(A2/60)', '=INT(A2/60)', 'B2', 39),
(258, 'Intermedio 1', 'TRUNCAR', 'Precio sin centavos', 'Trunca el precio en C3 a 0 decimales en D3.', '=TRUNCAR(C3,0)', '=TRUNC(C3,0)', 'D3', 39),
(259, 'Intermedio 1', 'SUMAPRODUCTO', 'Costo total de inventario', 'Calcula la suma de precios (D2:D9) multiplicados por stock (E2:E9) en F2.', '=SUMAPRODUCTO(D2:D9,E2:E9)', '=SUMPRODUCT(D2:D9,E2:E9)', 'F2', 39),
(260, 'Intermedio 1', 'RAIZ', 'Raíz de la suma', 'Calcula la raíz cuadrada de la suma de B2:B6 en C2.', '=RAIZ(SUMA(B2:B6))', '=SQRT(SUMA(B2:B6))||=SQRT(SUM(B2:B6))', 'C2', 40),

-- ══════════════════════════════════════════════════════
-- FUNCIONES FINANCIERAS (261-275)
-- ══════════════════════════════════════════════════════

(261, 'Avanzado 1', 'PAGO', 'Cuota mensual préstamo', 'Calcula la cuota mensual de un préstamo: tasa B2/12, periodos C2, valor presente D2 en E2.', '=PAGO(B2/12,C2,D2)', '=PMT(B2/12,C2,D2)', 'E2', 55),
(262, 'Avanzado 1', 'VA', 'Valor actual neto', 'Calcula el valor actual de un pago futuro: tasa B2/12, periodos C2, pago D2 en E2.', '=VA(B2/12,C2,D2)', '=PV(B2/12,C2,D2)', 'E2', 55),
(263, 'Avanzado 1', 'VF', 'Valor futuro ahorro', 'Calcula el valor futuro de una inversión: tasa B2/12, periodos C2, pago D2 en E2.', '=VF(B2/12,C2,D2)', '=FV(B2/12,C2,D2)', 'E2', 55),
(264, 'Avanzado 1', 'TASA', 'Tasa de interés mensual', 'Calcula la tasa mensual de interés con C2 periodos, pago D2 y valor presente E2 en F2.', '=TASA(C2,D2,E2)', '=RATE(C2,D2,E2)', 'F2', 56),
(265, 'Avanzado 1', 'NPER', 'Número de periodos', 'Calcula los periodos necesarios con tasa B2/12, pago C2 y valor presente D2 en E2.', '=NPER(B2/12,C2,D2)', '=NPER(B2/12,C2,D2)', 'E2', 56),
(266, 'Avanzado 1', 'PAGO', 'Cuota hipoteca anual', 'Calcula la cuota anual de hipoteca con tasa B3, 20 periodos y valor C3 en D3.', '=PAGO(B3,20,C3)', '=PMT(B3,20,C3)', 'D3', 56),
(267, 'Avanzado 1', 'VF', 'Ahorro final 12 meses', 'Calcula el valor futuro ahorrando D2 mensual, con tasa B2/12 y 12 periodos en E3.', '=VF(B2/12,12,-D2)', '=FV(B2/12,12,-D2)', 'E3', 57),
(268, 'Avanzado 1', 'PAGO', 'Cuota préstamo cero final', 'Cuota mensual de préstamo de 5 años: tasa B2/12, periodos 60, valor D2 en E4.', '=PAGO(B2/12,60,D2)', '=PMT(B2/12,60,D2)', 'E4', 57),
(269, 'Avanzado 1', 'PAGOINT', 'Interés primer mes', 'Calcula el interés del primer periodo con tasa B2/12, 1 de C2 periodos y valor D2 en E2.', '=PAGOINT(B2/12,1,C2,D2)', '=IPMT(B2/12,1,C2,D2)', 'E2', 57),
(270, 'Avanzado 1', 'PAGOPRIN', 'Amortización primer mes', 'Calcula la amortización del primer periodo con tasa B2/12, 1 de C2 periodos y valor D2 en E3.', '=PAGOPRIN(B2/12,1,C2,D2)', '=PPMT(B2/12,1,C2,D2)', 'E3', 58),
(271, 'Avanzado 1', 'VA', 'Valor actual de renta', 'Calcula el valor actual de pagos anuales de 1000, con tasa 5% y 10 periodos en E2.', '=VA(0.05,10,1000)', '=PV(0.05,10,1000)', 'E2', 58),
(272, 'Avanzado 1', 'TASA', 'Tasa efectiva anual', 'Calcula la tasa anual de un préstamo con 12 periodos, pago D2 y valor E2 en F3.', '=TASA(12,D2,E2)', '=RATE(12,D2,E2)', 'F3', 58),
(273, 'Avanzado 1', 'VF', 'Inversión sin pagos periódicos', 'Calcula el valor futuro de un capital inicial D2 a tasa B2 durante C2 años sin pagos adicionales en E2.', '=VF(B2,C2,0,-D2)', '=FV(B2,C2,0,-D2)', 'E2', 59),
(274, 'Avanzado 1', 'PAGO', 'Cuota crédito automotriz', 'Cuota mensual de auto: tasa mensual B2, 36 pagos, valor D2 en E5.', '=PAGO(B2,36,D2)', '=PMT(B2,36,D2)', 'E5', 59),
(275, 'Avanzado 1', 'NPER', 'Periodos para pagar deuda', 'Calcula periodos para pagar deuda a tasa B2, cuota C2 y deuda inicial D2 en E6.', '=NPER(B2,C2,D2)', '=NPER(B2,C2,D2)', 'E6', 59),

-- ══════════════════════════════════════════════════════
-- ESTADÍSTICAS (276-292)
-- ══════════════════════════════════════════════════════

(276, 'Intermedio 2', 'PROMEDIO.SI', 'Promedio condicional', 'Calcula el promedio de C2:C10 donde A2:A10 sea "Norte" en F2.', '=PROMEDIO.SI(A2:A10,"Norte",C2:C10)', '=AVERAGEIF(A2:A10,"Norte",C2:C10)', 'F2', 45),
(277, 'Intermedio 2', 'PROMEDIO.SI', 'Promedio de aprobados', 'Calcula el promedio de B2:B9 donde los valores son mayores o iguales a 60 en F3.', '=PROMEDIO.SI(B2:B9,">=60")', '=AVERAGEIF(B2:B9,">=60")', 'F3', 45),
(278, 'Intermedio 2', 'PROMEDIO.SI.CONJUNTO', 'Promedio dos criterios', 'Calcula el promedio de D2:D10 donde A2:A10 sea "Ventas" y E2:E10 sea "Activo" en G2.', '=PROMEDIO.SI.CONJUNTO(D2:D10,A2:A10,"Ventas",E2:E10,"Activo")', '=AVERAGEIFS(D2:D10,A2:A10,"Ventas",E2:E10,"Activo")', 'G2', 46),
(279, 'Intermedio 2', 'MEDIANA', 'Mediana del rango', 'Calcula la mediana de los valores en B2:B10 en C2.', '=MEDIANA(B2:B10)', '=MEDIAN(B2:B10)', 'C2', 46),
(280, 'Intermedio 2', 'MODA.UNO', 'Valor más frecuente', 'Devuelve el valor que más se repite en B2:B10 en C3.', '=MODA.UNO(B2:B10)', '=MODE(B2:B10)||=MODE.SNGL(B2:B10)', 'C3', 46),
(281, 'Intermedio 2', 'K.ESIMO.MAYOR', 'Segundo mayor valor', 'Devuelve el segundo valor más grande de B2:B10 en C4.', '=K.ESIMO.MAYOR(B2:B10,2)', '=LARGE(B2:B10,2)', 'C4', 47),
(282, 'Intermedio 2', 'K.ESIMO.MENOR', 'Tercer menor valor', 'Devuelve el tercer valor más pequeño de C2:C10 en D2.', '=K.ESIMO.MENOR(C2:C10,3)', '=SMALL(C2:C10,3)', 'D2', 47),
(283, 'Intermedio 2', 'DESVEST', 'Desviación estándar', 'Calcula la desviación estándar de B2:B10 en C5.', '=DESVEST(B2:B10)', '=STDEV(B2:B10)||=STDEV.M(B2:B10)', 'C5', 47),
(284, 'Intermedio 2', 'DESVEST.P', 'Desviación población', 'Calcula la desviación estándar de la población completa en B2:B10 en C6.', '=DESVEST.P(B2:B10)', '=STDEVP(B2:B10)||=STDEV.P(B2:B10)', 'C6', 48),
(285, 'Intermedio 2', 'VAR', 'Varianza muestral', 'Calcula la varianza de la muestra en B2:B10 en C7.', '=VAR(B2:B10)', '=VAR.S(B2:B10)', 'C7', 48),
(286, 'Intermedio 2', 'PERCENTIL', 'Percentil 75', 'Calcula el percentil 75 de los datos en B2:B10 en C8.', '=PERCENTIL(B2:B10,0.75)', '=PERCENTILE(B2:B10,0.75)||=PERCENTILE.INC(B2:B10,0.75)', 'C8', 48),
(287, 'Intermedio 2', 'CUARTIL', 'Tercer cuartil', 'Calcula el tercer cuartil (Q3) de B2:B10 en C9.', '=CUARTIL(B2:B10,3)', '=QUARTILE(B2:B10,3)||=QUARTILE.INC(B2:B10,3)', 'C9', 49),
(288, 'Intermedio 2', 'PROMEDIO.SI', 'Promedio de ventas altas', 'Calcula el promedio de B2:B8 donde los valores son mayores que 500 en F4.', '=PROMEDIO.SI(B2:B8,">500")', '=AVERAGEIF(B2:B8,">500")', 'F4', 49),
(289, 'Intermedio 2', 'K.ESIMO.MAYOR', 'Top 3 del rango', 'Devuelve el tercer valor más grande de D2:D10 en E2.', '=K.ESIMO.MAYOR(D2:D10,3)', '=LARGE(D2:D10,3)', 'E2', 49),
(290, 'Intermedio 2', 'MEDIANA', 'Mediana de calificaciones', 'Calcula la mediana de los valores en C2:C9 en D2.', '=MEDIANA(C2:C9)', '=MEDIAN(C2:C9)', 'D2', 50),
(291, 'Intermedio 2', 'PROMEDIO.SI.CONJUNTO', 'Promedio condicionado al sur', 'Calcula el promedio de C2:C10 donde A2:A10 sea "Sur" y D2:D10 sea mayor que 50 en G3.', '=PROMEDIO.SI.CONJUNTO(C2:C10,A2:A10,"Sur",D2:D10,">50")', '=AVERAGEIFS(C2:C10,A2:A10,"Sur",D2:D10,">50")', 'G3', 50),
(292, 'Intermedio 2', 'K.ESIMO.MENOR', 'Precio más bajo', 'Devuelve el menor precio de D2:D10 en E3.', '=K.ESIMO.MENOR(D2:D10,1)', '=SMALL(D2:D10,1)||=MIN(D2:D10)', 'E3', 50),

-- ══════════════════════════════════════════════════════
-- TEXTO AVANZADO (293-310)
-- ══════════════════════════════════════════════════════

(293, 'Experto 2', 'TEXTO', 'Formatear número', 'Formatea el número de B2 como moneda con 2 decimales en C2.', '=TEXTO(B2,"#,##0.00")', '=TEXT(B2,"#,##0.00")', 'C2', 53),
(294, 'Experto 2', 'TEXTO', 'Formatear fecha', 'Convierte la fecha de A2 al formato dd/mm/aaaa en B2.', '=TEXTO(A2,"dd/mm/aaaa")', '=TEXT(A2,"dd/mm/aaaa")', 'B2', 53),
(295, 'Experto 2', 'VALOR', 'Texto a número', 'Convierte el texto numérico de A2 a número en B2.', '=VALOR(A2)', '=VALUE(A2)', 'B2', 53),
(296, 'Experto 2', 'RECORTAR', 'Eliminar espacios', 'Elimina los espacios extra del texto en A2 en B2.', '=RECORTAR(A2)', '=TRIM(A2)', 'B2', 54),
(297, 'Experto 2', 'LIMPIAR', 'Limpiar caracteres', 'Elimina los caracteres no imprimibles del texto en A3 en B3.', '=LIMPIAR(A3)', '=CLEAN(A3)', 'B3', 54),
(298, 'Experto 2', 'REPETIR', 'Repetir texto', 'Repite el texto "-" cinco veces en B4.', '=REPETIR("-",5)', '=REPT("-",5)', 'B4', 54),
(299, 'Experto 2', 'HALLAR', 'Buscar posición texto', 'Encuentra la posición de "Excel" en A2 sin distinguir mayúsculas en B2.', '=HALLAR("Excel",A2)', '=SEARCH("Excel",A2)', 'B2', 55),
(300, 'Experto 2', 'REEMPLAZAR', 'Reemplazar caracteres', 'Reemplaza 3 caracteres de A2 a partir de la posición 5 con "XXX" en B2.', '=REEMPLAZAR(A2,5,3,"XXX")', '=REPLACE(A2,5,3,"XXX")', 'B2', 55),
(301, 'Experto 2', 'IGUAL', 'Comparar textos', 'Comprueba si el texto de A2 es exactamente igual al de B2 en C2.', '=IGUAL(A2,B2)', '=EXACT(A2,B2)', 'C2', 55),
(302, 'Experto 2', 'TEXTO', 'Número con ceros', 'Muestra el número de B2 con 4 dígitos, rellenando con ceros a la izquierda en C2.', '=TEXTO(B2,"0000")', '=TEXT(B2,"0000")', 'C2', 56),
(303, 'Experto 2', 'CONCATENAR', 'Unir con salto', 'Une A2 con espacio, B2, coma, espacio y C2 en D2.', '=CONCATENAR(A2," ",B2,", ",C2)', '=CONCAT(A2," ",B2,", ",C2)||=A2&" "&B2&", "&C2', 'D2', 56),
(304, 'Experto 2', 'RECORTAR', 'Limpiar nombre completo', 'Elimina espacios extras de la combinación de A2 y B2 en C2.', '=RECORTAR(CONCATENAR(A2," ",B2))', '=TRIM(CONCAT(A2," ",B2))||=TRIM(A2&" "&B2)', 'C2', 56),
(305, 'Experto 2', 'VALOR', 'Sumar texto numérico', 'Suma los valores numéricos en texto de A2 y A3 convirtiéndolos primero en C2.', '=VALOR(A2)+VALOR(A3)', '=VALUE(A2)+VALUE(A3)', 'C2', 57),
(306, 'Experto 2', 'REPETIR', 'Barra de progreso texto', 'Crea una barra repitiendo "|" tantas veces como indica B2 en C2.', '=REPETIR("|",B2)', '=REPT("|",B2)', 'C2', 57),
(307, 'Experto 2', 'TEXTO', 'Porcentaje formateado', 'Formatea B2 como porcentaje con 1 decimal en C2.', '=TEXTO(B2,"0.0%")', '=TEXT(B2,"0.0%")', 'C2', 57),
(308, 'Experto 2', 'HALLAR', 'Extraer texto antes del guion', 'Extrae los caracteres antes del primer "-" en A2 en B2.', '=IZQUIERDA(A2,HALLAR("-",A2)-1)', '=LEFT(A2,SEARCH("-",A2)-1)', 'B2', 58),
(309, 'Experto 2', 'SUSTITUIR', 'Reemplazar espacios', 'Sustituye todos los espacios en A2 por guiones bajos en B2.', '=SUSTITUIR(A2," ","_")', '=SUBSTITUTE(A2," ","_")', 'B2', 58),
(310, 'Experto 2', 'LARGO', 'Largo con recorte', 'Calcula el largo del texto de A2 después de eliminar espacios extra en B2.', '=LARGO(RECORTAR(A2))', '=LEN(TRIM(A2))', 'B2', 58),

-- ══════════════════════════════════════════════════════
-- SI ANIDADO Y LÓGICAS AVANZADAS (311-325)
-- ══════════════════════════════════════════════════════

(311, 'Experto 2', 'SI anidado', 'Tres niveles', 'Si B2 es mayor o igual a 90 muestra "A", si es mayor o igual a 70 muestra "B", si no muestra "C" en C2.', '=SI(B2>=90,"A",SI(B2>=70,"B","C"))', NULL, 'C2', 59),
(312, 'Experto 2', 'SI anidado', 'Cuatro niveles de calificación', 'Si B2 es mayor o igual a 90 muestra "Excelente", si mayor o igual a 75 "Bueno", si mayor o igual a 60 "Regular", si no "Insuficiente" en C3.', '=SI(B2>=90,"Excelente",SI(B2>=75,"Bueno",SI(B2>=60,"Regular","Insuficiente")))', NULL, 'C3', 59),
(313, 'Experto 1', 'NO', 'Negación lógica', 'Muestra VERDADERO si A2 NO es mayor que 100 en B2.', '=NO(A2>100)', '=NOT(A2>100)', 'B2', 48),
(314, 'Experto 1', 'Y', 'Múltiples condiciones Y', 'Si A2 es mayor que 0 Y B2 es mayor que 0 Y C2 es mayor que 0, muestra "Todo positivo", si no "Hay negativos" en D2.', '=SI(Y(A2>0,B2>0,C2>0),"Todo positivo","Hay negativos")', NULL, 'D2', 48),
(315, 'Experto 1', 'O', 'Condición O múltiple', 'Si A2 es "Rojo" O A2 es "Verde" O A2 es "Azul", muestra "Color primario", si no "Otro" en B2.', '=SI(O(A2="Rojo",A2="Verde",A2="Azul"),"Color primario","Otro")', NULL, 'B2', 49),
(316, 'Experto 2', 'SI anidado', 'Descuento por volumen', 'Si B2 es mayor que 100 muestra 0.15, si mayor que 50 muestra 0.10, si mayor que 20 muestra 0.05, si no 0 en C2.', '=SI(B2>100,0.15,SI(B2>50,0.10,SI(B2>20,0.05,0)))', NULL, 'C2', 60),
(317, 'Experto 2', 'SI anidado', 'Clasificación IMC', 'Si A2 es menor que 18.5 muestra "Bajo peso", si menor que 25 "Normal", si menor que 30 "Sobrepeso", si no "Obesidad" en B2.', '=SI(A2<18.5,"Bajo peso",SI(A2<25,"Normal",SI(A2<30,"Sobrepeso","Obesidad")))', NULL, 'B2', 60),
(318, 'Experto 1', 'SI + Y + O', 'Combinación compleja', 'Si A2 es "Norte" Y (B2 es mayor que 500 O C2 es mayor que 300), muestra "Prioritario", si no "Normal" en D2.', '=SI(Y(A2="Norte",O(B2>500,C2>300)),"Prioritario","Normal")', NULL, 'D2', 60),
(319, 'Experto 2', 'SI.ERROR', 'Manejar error división', 'Si B2 dividido entre C2 produce error, muestra "Error", si no muestra el resultado en D2.', '=SI.ERROR(B2/C2,"Error")', '=IFERROR(B2/C2,"Error")', 'D2', 61),
(320, 'Experto 2', 'SI.ERROR', 'Proteger BUSCARV', 'Usa BUSCARV para buscar A2 en F2:G10 columna 2 exacto; si hay error muestra "No encontrado" en E2.', '=SI.ERROR(BUSCARV(A2,F2:G10,2,0),"No encontrado")', '=IFERROR(VLOOKUP(A2,F2:G10,2,0),"No encontrado")', 'E2', 61),
(321, 'Experto 1', 'Y', 'Requisito de bono', 'Si D2 es mayor que 1000 Y E2 es mayor que 5, muestra "Bono"; si no "Sin bono" en F2.', '=SI(Y(D2>1000,E2>5),"Bono","Sin bono")', NULL, 'F2', 51),
(322, 'Experto 2', 'SI anidado', 'Nivel de riesgo', 'Si C2 es mayor que 80 muestra "Alto", si mayor que 50 "Medio", si mayor que 20 "Bajo", si no "Mínimo" en D2.', '=SI(C2>80,"Alto",SI(C2>50,"Medio",SI(C2>20,"Bajo","Mínimo")))', NULL, 'D2', 61),
(323, 'Experto 2', 'SI.ND', 'Controlar #N/A', 'Usa COINCIDIR para buscar A2 en B2:B10 exacto; si da #N/A muestra 0 en C2.', '=SI.ND(COINCIDIR(A2,B2:B10,0),0)', '=IFNA(MATCH(A2,B2:B10,0),0)', 'C2', 62),
(324, 'Experto 1', 'NO', 'No está activo', 'Si A2 NO es igual a "Activo", muestra "Inactivo"; si no "OK" en B2.', '=SI(NO(A2="Activo"),"Inactivo","OK")', '=SI(NOT(A2="Activo"),"Inactivo","OK")', 'B2', 52),
(325, 'Experto 2', 'SI anidado', 'Estado de pago', 'Si C2 es mayor que 0 Y D2 es igual a "Pagado" muestra "OK", si D2 es "Pendiente" muestra "Pendiente", si no "Revisar" en E2.', '=SI(Y(C2>0,D2="Pagado"),"OK",SI(D2="Pendiente","Pendiente","Revisar"))', NULL, 'E2', 62),

-- ══════════════════════════════════════════════════════
-- BUSCARV Y BUSCARX AVANZADOS (326-345)
-- ══════════════════════════════════════════════════════

(326, 'Intermedio 2', 'BUSCARV', 'BUSCARV básico exacto', 'Busca el valor de A2 en la tabla D2:F10 y devuelve la columna 2 con coincidencia exacta en B2.', '=BUSCARV(A2,D2:F10,2,0)', '=VLOOKUP(A2,D2:F10,2,0)||=VLOOKUP(A2,D2:F10,2,FALSE)', 'B2', 48),
(327, 'Intermedio 2', 'BUSCARV', 'BUSCARV tercera columna', 'Busca A3 en D2:G10 y devuelve la tercera columna con coincidencia exacta en B3.', '=BUSCARV(A3,D2:G10,3,0)', '=VLOOKUP(A3,D2:G10,3,0)||=VLOOKUP(A3,D2:G10,3,FALSE)', 'B3', 48),
(328, 'Intermedio 2', 'BUSCARV', 'BUSCARV con aproximación', 'Busca B2 en E2:F10 ordenado y devuelve la columna 2 con coincidencia aproximada en C2.', '=BUSCARV(B2,E2:F10,2,1)', '=VLOOKUP(B2,E2:F10,2,1)||=VLOOKUP(B2,E2:F10,2,TRUE)', 'C2', 49),
(329, 'Experto 1', 'BUSCARV', 'BUSCARV protegido', 'Busca A4 en $D$2:$F$10 y devuelve columna 2 exacto; si da error muestra 0 en B4.', '=SI.ERROR(BUSCARV(A4,$D$2:$F$10,2,0),0)', '=IFERROR(VLOOKUP(A4,$D$2:$F$10,2,0),0)', 'B4', 52),
(330, 'Experto 1', 'BUSCARV', 'BUSCARV con columna variable', 'Busca A2 en D2:H10 y devuelve la columna indicada en B1 con coincidencia exacta en C2.', '=BUSCARV(A2,D2:H10,B1,0)', '=VLOOKUP(A2,D2:H10,B1,0)', 'C2', 52),
(331, 'Experto 2', 'BUSCARX', 'BUSCARX básico', 'Busca A2 en D2:D10 y devuelve el valor correspondiente de E2:E10 en B2.', '=BUSCARX(A2,D2:D10,E2:E10)', '=XLOOKUP(A2,D2:D10,E2:E10)', 'B2', 60),
(332, 'Experto 2', 'BUSCARX', 'BUSCARX con valor por defecto', 'Busca A3 en D2:D10 y devuelve E2:E10; si no encuentra devuelve "Sin datos" en B3.', '=BUSCARX(A3,D2:D10,E2:E10,"Sin datos")', '=XLOOKUP(A3,D2:D10,E2:E10,"Sin datos")', 'B3', 60),
(333, 'Experto 2', 'BUSCARX', 'BUSCARX columna diferente', 'Busca A2 en B2:B10 y devuelve el valor de D2:D10 en E2.', '=BUSCARX(A2,B2:B10,D2:D10)', '=XLOOKUP(A2,B2:B10,D2:D10)', 'E2', 61),
(334, 'Experto 2', 'BUSCARX', 'BUSCARX último encontrado', 'Busca A2 en D2:D10 y devuelve E2:E10; si hay varios toma el último (modo -1) en B4.', '=BUSCARX(A2,D2:D10,E2:E10,,,-1)', '=XLOOKUP(A2,D2:D10,E2:E10,,,-1)', 'B4', 62),
(335, 'Experto 1', 'BUSCARV', 'Precio por nombre', 'Busca el nombre de G2 en A2:C10 y devuelve la columna 3 exacta en H2.', '=BUSCARV(G2,A2:C10,3,0)', '=VLOOKUP(G2,A2:C10,3,0)', 'H2', 53),
(336, 'Experto 1', 'BUSCARV', 'Categoría por código', 'Busca el código de F2 en A2:D10 y devuelve la columna 2 exacta en G2.', '=BUSCARV(F2,A2:D10,2,0)', '=VLOOKUP(F2,A2:D10,2,0)', 'G2', 53),
(337, 'Experto 2', 'BUSCARX', 'BUSCARX bidireccional', 'Busca A2 en A1:A10 (encabezados en fila) y devuelve B1:B10 en C2.', '=BUSCARX(A2,A1:A10,B1:B10)', '=XLOOKUP(A2,A1:A10,B1:B10)', 'C2', 62),
(338, 'Experto 2', 'INDICE + COINCIDIR', 'Búsqueda con referencia cruzada', 'Usa INDICE y COINCIDIR para buscar el valor de H2 en A2:A10 y devolver F2:F10 en I2.', '=INDICE(F2:F10,COINCIDIR(H2,A2:A10,0))', '=INDEX(F2:F10,MATCH(H2,A2:A10,0))', 'I2', 63),
(339, 'Experto 2', 'INDICE + COINCIDIR', 'Precio más alto por nombre', 'Devuelve el nombre de A2:A10 con el precio máximo en D2:D10 en G2.', '=INDICE(A2:A10,COINCIDIR(MAX(D2:D10),D2:D10,0))', '=INDEX(A2:A10,MATCH(MAX(D2:D10),D2:D10,0))', 'G2', 63),
(340, 'Experto 3', 'BUSCARX', 'BUSCARX con comodín', 'Busca cualquier texto que contenga el valor de A2 en D2:D10 (modo 2) y devuelve E2:E10 en B2.', '=BUSCARX(A2,D2:D10,E2:E10,,2)', '=XLOOKUP(A2,D2:D10,E2:E10,,2)', 'B2', 68),
(341, 'Experto 2', 'BUSCARV', 'BUSCARV con COINCIDIR', 'Busca A2 en D2:H10 y devuelve la columna indicada por COINCIDIR de H1 en D1:H1 exacto en B2.', '=BUSCARV(A2,D2:H10,COINCIDIR(H1,D1:H1,0),0)', '=VLOOKUP(A2,D2:H10,MATCH(H1,D1:H1,0),0)', 'B2', 65),
(342, 'Experto 2', 'INDICE + COINCIDIR', 'Tabla bidimensional', 'Usa INDICE para devolver el valor de B2:E10 en la fila de COINCIDIR de A2 en A2:A10 y columna de COINCIDIR de B1 en B1:E1 en F2.', '=INDICE(B2:E10,COINCIDIR(A2,A2:A10,0),COINCIDIR(B1,B1:E1,0))', '=INDEX(B2:E10,MATCH(A2,A2:A10,0),MATCH(B1,B1:E1,0))', 'F2', 66),
(343, 'Experto 2', 'BUSCARV', 'Interpolación aproximada', 'Busca el valor de A2 en la tabla D2:E10 ordenada y devuelve la columna 2 con aproximación en B2.', '=BUSCARV(A2,D2:E10,2,1)', '=VLOOKUP(A2,D2:E10,2,TRUE)', 'B2', 63),
(344, 'Experto 2', 'SI.ERROR + BUSCARV', 'Doble BUSCARV con error', 'Busca A2 en D2:E10 col 2 exacto; si hay error, busca A2 en F2:G10 col 2 exacto en B2.', '=SI.ERROR(BUSCARV(A2,D2:E10,2,0),BUSCARV(A2,F2:G10,2,0))', '=IFERROR(VLOOKUP(A2,D2:E10,2,0),VLOOKUP(A2,F2:G10,2,0))', 'B2', 65),
(345, 'Experto 3', 'BUSCARX', 'BUSCARX con múltiple retorno', 'Busca A2 en D2:D10 y devuelve el rango E2:F10 en B2.', '=BUSCARX(A2,D2:D10,E2:F10)', '=XLOOKUP(A2,D2:D10,E2:F10)', 'B2', 68),

-- ══════════════════════════════════════════════════════
-- COMBINACIONES MAESTRAS (346-370)
-- ══════════════════════════════════════════════════════

(346, 'Experto 3', 'SUMAPRODUCTO condicional', 'Sumar si con SUMAPRODUCTO', 'Usa SUMAPRODUCTO para sumar C2:C10 donde A2:A10 sea "Norte" en F2.', '=SUMAPRODUCTO((A2:A10="Norte")*C2:C10)', '=SUMPRODUCT((A2:A10="Norte")*C2:C10)', 'F2', 68),
(347, 'Experto 3', 'SUMAPRODUCTO condicional', 'Contar con dos criterios', 'Usa SUMAPRODUCTO para contar filas donde A2:A10 sea "Norte" y B2:B10 sea mayor que 100 en G2.', '=SUMAPRODUCTO((A2:A10="Norte")*(B2:B10>100))', '=SUMPRODUCT((A2:A10="Norte")*(B2:B10>100))', 'G2', 68),
(348, 'Experto 3', 'SUMAPRODUCTO condicional', 'Suma con condición numérica', 'Usa SUMAPRODUCTO para sumar D2:D10 donde C2:C10 sea mayor que 50 y E2:E10 sea "Activo" en F3.', '=SUMAPRODUCTO((C2:C10>50)*(E2:E10="Activo")*D2:D10)', '=SUMPRODUCT((C2:C10>50)*(E2:E10="Activo")*D2:D10)', 'F3', 69),
(349, 'Experto 3', 'BUSCARV + SUMAR.SI', 'Subtotal por categoría', 'Suma C2:C10 donde B2:B10 sea igual al resultado de BUSCARV de A12 en D2:E5 col 2 exacto en F12.', '=SUMAR.SI(B2:B10,BUSCARV(A12,D2:E5,2,0),C2:C10)', '=SUMIF(B2:B10,VLOOKUP(A12,D2:E5,2,0),C2:C10)', 'F12', 70),
(350, 'Experto 3', 'INDICE + COINCIDIR + GRANDE', 'Nombre del top 1', 'Devuelve el nombre de A2:A10 con el mayor valor en B2:B10 usando INDICE y COINCIDIR en G2.', '=INDICE(A2:A10,COINCIDIR(MAX(B2:B10),B2:B10,0))', '=INDEX(A2:A10,MATCH(MAX(B2:B10),B2:B10,0))', 'G2', 70),
(351, 'Experto 3', 'PROMEDIO.SI.CONJUNTO', 'Promedio ventas activas al norte', 'Calcula el promedio de C2:C10 donde A2:A10 sea "Norte" y E2:E10 sea "Activo" en G4.', '=PROMEDIO.SI.CONJUNTO(C2:C10,A2:A10,"Norte",E2:E10,"Activo")', '=AVERAGEIFS(C2:C10,A2:A10,"Norte",E2:E10,"Activo")', 'G4', 70),
(352, 'Experto 3', 'SI.CONJUNTO + CONTAR.SI', 'Evaluación dinámica', 'Si CONTAR.SI de A2:A10 "Norte" es mayor que 3 muestra "Muchos", si es mayor que 1 "Algunos", si no "Pocos" en G5.', '=SI.CONJUNTO(CONTAR.SI(A2:A10,"Norte")>3,"Muchos",CONTAR.SI(A2:A10,"Norte")>1,"Algunos",VERDADERO,"Pocos")', '=IFS(COUNTIF(A2:A10,"Norte")>3,"Muchos",COUNTIF(A2:A10,"Norte")>1,"Algunos",TRUE,"Pocos")', 'G5', 71),
(353, 'Experto 3', 'SUMAPRODUCTO condicional', 'Promedio condicional manual', 'Calcula el promedio de C2:C10 donde A2:A10 sea "Sur" dividiendo SUMAPRODUCTO por CONTAR.SI en G6.', '=SUMAPRODUCTO((A2:A10="Sur")*C2:C10)/CONTAR.SI(A2:A10,"Sur")', '=SUMPRODUCT((A2:A10="Sur")*C2:C10)/COUNTIF(A2:A10,"Sur")', 'G6', 71),
(354, 'Experto 3', 'INDICE + COINCIDIR', 'Mínimo por categoría', 'Devuelve el nombre de A2:A10 con el menor valor en C2:C10 en G7.', '=INDICE(A2:A10,COINCIDIR(MIN(C2:C10),C2:C10,0))', '=INDEX(A2:A10,MATCH(MIN(C2:C10),C2:C10,0))', 'G7', 71),
(355, 'Experto 3', 'BUSCARX + SI.ERROR', 'Búsqueda en dos tablas', 'Busca A2 en D2:D10 y devuelve E2:E10; si no encuentra busca en F2:F10 y devuelve G2:G10 en B2.', '=SI.ERROR(BUSCARX(A2,D2:D10,E2:E10),BUSCARX(A2,F2:F10,G2:G10))', '=IFERROR(XLOOKUP(A2,D2:D10,E2:E10),XLOOKUP(A2,F2:F10,G2:G10))', 'B2', 72),
(356, 'Experto 3', 'SUMAPRODUCTO condicional', 'Porcentaje de participación', 'Calcula el porcentaje que representa la suma de B2:B5 sobre SUMA de B2:B10 en C2.', '=SUMA(B2:B5)/SUMA(B2:B10)', '=SUM(B2:B5)/SUM(B2:B10)', 'C2', 70),
(357, 'Experto 3', 'INDICE + COINCIDIR', 'Segundo mayor en lista', 'Devuelve el nombre de A2:A10 con el segundo mayor valor en B2:B10 en G8.', '=INDICE(A2:A10,COINCIDIR(K.ESIMO.MAYOR(B2:B10,2),B2:B10,0))', '=INDEX(A2:A10,MATCH(LARGE(B2:B10,2),B2:B10,0))', 'G8', 72),
(358, 'Experto 3', 'BUSCARV + REDONDEAR', 'Precio con descuento redondeado', 'Busca A2 en D2:E10 col 2 exacto, multiplica por 0.9 y redondea a 2 decimales en B2.', '=REDONDEAR(BUSCARV(A2,D2:E10,2,0)*0.9,2)', '=ROUND(VLOOKUP(A2,D2:E10,2,0)*0.9,2)', 'B2', 72),
(359, 'Experto 3', 'SUMAR.SI + CONTAR.SI', 'Promedio condicional básico', 'Calcula el promedio condicional dividiendo SUMAR.SI de B2:B10 "Norte" en C2:C10 entre CONTAR.SI de B2:B10 "Norte" en G9.', '=SUMAR.SI(B2:B10,"Norte",C2:C10)/CONTAR.SI(B2:B10,"Norte")', '=SUMIF(B2:B10,"Norte",C2:C10)/COUNTIF(B2:B10,"Norte")', 'G9', 73),
(360, 'Experto 3', 'CONTAR.SI.CONJUNTO + SI', 'Alerta de stock múltiple', 'Si CONTAR.SI.CONJUNTO de A2:A10 "Redes" y C2:C10 menor a 10 es mayor que 0, muestra "Revisar stock"; si no "OK" en G10.', '=SI(CONTAR.SI.CONJUNTO(A2:A10,"Redes",C2:C10,"<10")>0,"Revisar stock","OK")', '=IF(COUNTIFS(A2:A10,"Redes",C2:C10,"<10")>0,"Revisar stock","OK")', 'G10', 73),

-- ══════════════════════════════════════════════════════
-- FUNCIONES MISCELÁNEAS Y MODERNAS (361-380)
-- ══════════════════════════════════════════════════════

(361, 'Básico', 'CONTARA', 'Contar celdas no vacías', 'Cuenta las celdas no vacías en el rango A2:A10 en B2.', '=CONTARA(A2:A10)', '=COUNTA(A2:A10)', 'B2', 15),
(362, 'Básico', 'CONTAR.BLANCO', 'Contar celdas vacías', 'Cuenta las celdas vacías en A2:A10 en B3.', '=CONTAR.BLANCO(A2:A10)', '=COUNTBLANK(A2:A10)', 'B3', 15),
(363, 'Básico', 'CONTAR', 'Contar solo números', 'Cuenta cuántas celdas con números hay en B2:B10 en C2.', '=CONTAR(B2:B10)', '=COUNT(B2:B10)', 'C2', 15),
(364, 'Básico', 'MAX + MIN', 'Rango de valores', 'Calcula la diferencia entre el máximo y el mínimo de B2:B10 en C3.', '=MAX(B2:B10)-MIN(B2:B10)', NULL, 'C3', 16),
(365, 'Básico', 'SUMA + CONTAR', 'Promedio manual', 'Divide la SUMA de B2:B6 entre el CONTAR de B2:B6 en C4.', '=SUMA(B2:B6)/CONTAR(B2:B6)', '=SUM(B2:B6)/COUNT(B2:B6)', 'C4', 16),
(366, 'Intermedio 1', 'ELEGIR', 'Seleccionar por índice', 'Devuelve el texto correspondiente al índice en A2: 1="Lunes", 2="Martes", 3="Miércoles" en B2.', '=ELEGIR(A2,"Lunes","Martes","Miércoles")', '=CHOOSE(A2,"Lunes","Martes","Miércoles")', 'B2', 35),
(367, 'Intermedio 1', 'ELEGIR', 'Trimestre del año', 'Devuelve el trimestre según el número en A2: 1="Q1", 2="Q2", 3="Q3", 4="Q4" en B2.', '=ELEGIR(A2,"Q1","Q2","Q3","Q4")', '=CHOOSE(A2,"Q1","Q2","Q3","Q4")', 'B2', 35),
(368, 'Intermedio 1', 'AHORA + HORA', 'Extraer hora actual', 'Extrae la hora del valor de fecha-hora en A2 en B2.', '=HORA(A2)', '=HOUR(A2)', 'B2', 36),
(369, 'Intermedio 1', 'MINUTO', 'Extraer minutos', 'Extrae los minutos del valor de fecha-hora en A3 en B3.', '=MINUTO(A3)', '=MINUTE(A3)', 'B3', 36),
(370, 'Intermedio 1', 'SEGUNDO', 'Extraer segundos', 'Extrae los segundos del valor de fecha-hora en A4 en B4.', '=SEGUNDO(A4)', '=SECOND(A4)', 'B4', 36),
(371, 'Intermedio 1', 'SI.ERROR + PROMEDIO', 'Promedio protegido', 'Calcula el promedio de B2:B10; si hay error muestra 0 en C2.', '=SI.ERROR(PROMEDIO(B2:B10),0)', '=IFERROR(AVERAGE(B2:B10),0)', 'C2', 37),
(372, 'Intermedio 1', 'CONTAR', 'Contar registros de ventas', 'Cuenta los números en D2:D9 en E2.', '=CONTAR(D2:D9)', '=COUNT(D2:D9)', 'E2', 20),
(373, 'Básico', 'PROMEDIO', 'Promedio simple', 'Calcula el promedio de C2:C8 en D2.', '=PROMEDIO(C2:C8)', '=AVERAGE(C2:C8)', 'D2', 12),
(374, 'Básico', 'MAX', 'Máximo de ventas', 'Devuelve el valor máximo del rango D2:D10 en E2.', '=MAX(D2:D10)', NULL, 'E2', 12),
(375, 'Básico', 'MIN', 'Mínimo de stock', 'Devuelve el valor mínimo del rango E2:E10 en F2.', '=MIN(E2:E10)', NULL, 'F2', 12),

-- ══════════════════════════════════════════════════════
-- MÁS VARIEDAD: BÁSICOS Y VARIADOS (376-400)
-- ══════════════════════════════════════════════════════

(376, 'Básico', 'SUMA', 'Suma de tres columnas', 'Suma los rangos B2:B6, C2:C6 y D2:D6 en la celda E2.', '=SUMA(B2:B6,C2:C6,D2:D6)', '=SUM(B2:B6,C2:C6,D2:D6)', 'E2', 13),
(377, 'Básico', 'SUMA', 'Gran total', 'Suma todos los valores del rango B2:D10 en la celda F2.', '=SUMA(B2:D10)', '=SUM(B2:D10)', 'F2', 13),
(378, 'Básico', 'MULTIPLICACIÓN', 'Factura con IVA', 'Multiplica B2 por C2 y el resultado por 1.21 para incluir IVA en D2.', '=B2*C2*1.21', NULL, 'D2', 14),
(379, 'Básico', 'DIVISIÓN', 'Proporción', 'Calcula cuánto representa B2 del total SUMA(B2:B10) en C2.', '=B2/SUMA(B2:B10)', '=B2/SUM(B2:B10)', 'C2', 14),
(380, 'Intermedio 1', 'SI', 'Semáforo de stock', 'Si C2 es menor que 10 muestra "Rojo", si menor que 50 "Amarillo", si no "Verde" en D2.', '=SI(C2<10,"Rojo",SI(C2<50,"Amarillo","Verde"))', NULL, 'D2', 30),
(381, 'Intermedio 1', 'SUMAR.SI', 'Total de una categoría', 'Suma los valores de C2:C10 donde B2:B10 sea "Electrónica" en D2.', '=SUMAR.SI(B2:B10,"Electrónica",C2:C10)', '=SUMIF(B2:B10,"Electrónica",C2:C10)', 'D2', 30),
(382, 'Intermedio 1', 'CONTAR.SI', 'Contar productos caros', 'Cuenta cuántos valores en D2:D10 son mayores que 800 en E2.', '=CONTAR.SI(D2:D10,">800")', '=COUNTIF(D2:D10,">800")', 'E2', 30),
(383, 'Intermedio 1', 'MAX + SI', 'Máximo condicional aproximado', 'Si el MAX de B2:B10 es mayor que 1000 muestra "Muy alto"; si no muestra el MAX en C2.', '=SI(MAX(B2:B10)>1000,"Muy alto",MAX(B2:B10))', NULL, 'C2', 31),
(384, 'Intermedio 1', 'BUSCARV', 'Descripción por código', 'Busca el valor de A2 en la tabla F2:H10 y devuelve la descripción (columna 2) exacta en B2.', '=BUSCARV(A2,F2:H10,2,0)', '=VLOOKUP(A2,F2:H10,2,0)||=VLOOKUP(A2,F2:H10,2,FALSE)', 'B2', 45),
(385, 'Intermedio 1', 'PROMEDIO.SI', 'Promedio de calificaciones altas', 'Calcula el promedio de C2:C10 donde los valores son mayores o iguales a 80 en D2.', '=PROMEDIO.SI(C2:C10,">=80")', '=AVERAGEIF(C2:C10,">=80")', 'D2', 43),
(386, 'Básico', 'SI', 'Aprobado o reprobado', 'Si B2 es mayor o igual a 60 muestra "Aprobado", si no "Reprobado" en C2.', '=SI(B2>=60,"Aprobado","Reprobado")', '=IF(B2>=60,"Aprobado","Reprobado")', 'C2', 20),
(387, 'Básico', 'SI', 'Bonificación simple', 'Si C2 es mayor que 500 muestra 50, si no muestra 0 en D2.', '=SI(C2>500,50,0)', '=IF(C2>500,50,0)', 'D2', 20),
(388, 'Intermedio 1', 'CONTAR.SI', 'Cuántos aprobados', 'Cuenta cuántos valores de B2:B10 son mayores o iguales a 70 en C2.', '=CONTAR.SI(B2:B10,">=70")', '=COUNTIF(B2:B10,">=70")', 'C2', 28),
(389, 'Intermedio 1', 'SUMAR.SI', 'Suma de valores positivos', 'Suma los valores de B2:B10 que sean mayores que 0 en C2.', '=SUMAR.SI(B2:B10,">0")', '=SUMIF(B2:B10,">0")', 'C2', 28),
(390, 'Intermedio 2', 'COINCIDIR', 'Posición del valor buscado', 'Encuentra la posición de G2 en A2:A10 con coincidencia exacta en H2.', '=COINCIDIR(G2,A2:A10,0)', '=MATCH(G2,A2:A10,0)', 'H2', 42),
(391, 'Intermedio 2', 'INDICE', 'Valor en posición dada', 'Devuelve el valor de la posición H2 en el rango B2:B10 en I2.', '=INDICE(B2:B10,H2)', '=INDEX(B2:B10,H2)', 'I2', 42),
(392, 'Experto 1', 'REDONDEAR', 'Precio con IVA redondeado', 'Multiplica B2 por 1.21 y redondea a 2 decimales en C2.', '=REDONDEAR(B2*1.21,2)', '=ROUND(B2*1.21,2)', 'C2', 47),
(393, 'Experto 1', 'CONCATENAR', 'ID con nombre', 'Une "ID-" con el valor de A2 y "-" y B2 en C2.', '=CONCATENAR("ID-",A2,"-",B2)', '=CONCAT("ID-",A2,"-",B2)||="ID-"&A2&"-"&B2', 'C2', 50),
(394, 'Experto 1', 'DERECHA', 'Extraer extensión', 'Extrae los últimos 3 caracteres de A2 en B2.', '=DERECHA(A2,3)', '=RIGHT(A2,3)', 'B2', 50),
(395, 'Básico', 'SUMA', 'Total parcial', 'Suma los valores de B2 hasta B5 en la celda C2.', '=SUMA(B2:B5)', '=B2+B3+B4+B5||=SUM(B2:B5)', 'C2', 10),
(396, 'Básico', 'PROMEDIO', 'Media de notas', 'Calcula el promedio de B2, C2 y D2 en E2.', '=PROMEDIO(B2:D2)', '=AVERAGE(B2:D2)||=(B2+C2+D2)/3', 'E2', 10),
(397, 'Básico', 'MAX', 'Mejor resultado', 'Devuelve el mayor valor entre B3, C3 y D3 en E3.', '=MAX(B3:D3)', '=MAX(B3,C3,D3)', 'E3', 11),
(398, 'Básico', 'MIN', 'Peor resultado', 'Devuelve el menor valor entre B4, C4 y D4 en E4.', '=MIN(B4:D4)', '=MIN(B4,C4,D4)', 'E4', 11),
(399, 'Intermedio 1', 'SI', 'Categoría por precio', 'Si D2 es mayor que 1000 muestra "Premium", si mayor que 300 "Estándar", si no "Económico" en E2.', '=SI(D2>1000,"Premium",SI(D2>300,"Estándar","Económico"))', NULL, 'E2', 32),
(400, 'Experto 3', 'SUMAPRODUCTO condicional', 'Ingreso neto por zona', 'Usa SUMAPRODUCTO para calcular (B2:B10 - C2:C10) donde A2:A10 sea "Norte" en F2.', '=SUMAPRODUCTO((A2:A10="Norte")*(B2:B10-C2:C10))', '=SUMPRODUCT((A2:A10="Norte")*(B2:B10-C2:C10))', 'F2', 73);

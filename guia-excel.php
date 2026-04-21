<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/layout.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <?php render_head(
        APP_NAME . ' | Guía de Fórmulas Excel — de Básico a Experto',
        'Guía completa de fórmulas de Excel: SUMA, PROMEDIO, SI, BUSCARV, SUMAR.SI, CONTAR.SI, BUSCARX y más. Aprende con ejemplos reales y practica con Excel Snake, 100% gratis.'
    ); ?>
    <style>
        /* ── Guide page layout ── */
        .guide-hero {
            text-align: center;
            padding: 3.5rem 1rem 2rem;
            position: relative;
            z-index: 1;
        }
        .guide-hero .eyebrow { color: #22c55e; font-weight: 700; font-size: 0.85rem; letter-spacing: 0.1em; text-transform: uppercase; }
        .guide-hero h1 { font-size: clamp(1.8rem, 5vw, 3rem); font-weight: 900; margin: 0.5rem 0 1rem; }
        .guide-hero p { color: #94a3b8; max-width: 640px; margin: 0 auto 1.5rem; font-size: 1.05rem; line-height: 1.7; }

        .guide-toc {
            max-width: 760px; margin: 0 auto 2.5rem; padding: 1.5rem;
            background: rgba(30,41,59,0.6); border: 1px solid rgba(255,255,255,0.06);
            border-radius: 1rem; backdrop-filter: blur(8px);
        }
        .guide-toc h2 { font-size: 1rem; font-weight: 700; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.08em; margin: 0 0 1rem; }
        .guide-toc ol { margin: 0; padding-left: 1.4rem; display: grid; gap: 0.35rem; }
        .guide-toc li a { color: #60a5fa; text-decoration: none; font-size: 0.95rem; }
        .guide-toc li a:hover { color: #93c5fd; text-decoration: underline; }

        .guide-main {
            max-width: 820px;
            margin: 0 auto;
            padding: 0 1rem 4rem;
        }

        .guide-section {
            margin-bottom: 3rem;
            scroll-margin-top: 80px;
        }

        .guide-section-header {
            display: flex; align-items: center; gap: 0.75rem;
            padding-bottom: 0.75rem;
            border-bottom: 1px solid rgba(255,255,255,0.08);
            margin-bottom: 1.5rem;
        }
        .guide-section-header .guide-icon {
            width: 44px; height: 44px; border-radius: 0.75rem;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.25rem; flex-shrink: 0;
        }
        .guide-section-header h2 { font-size: 1.4rem; font-weight: 800; margin: 0; }

        .guide-formula-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(340px, 1fr));
            gap: 1.25rem;
        }

        .formula-card {
            background: rgba(30,41,59,0.55);
            border: 1px solid rgba(255,255,255,0.07);
            border-radius: 1rem;
            padding: 1.25rem;
            transition: border-color 0.2s, transform 0.2s;
        }
        .formula-card:hover { border-color: rgba(99,102,241,0.35); transform: translateY(-2px); }

        .formula-card h3 {
            font-size: 1.05rem; font-weight: 800; margin: 0 0 0.4rem;
            display: flex; align-items: center; gap: 0.5rem;
        }
        .formula-card .fc-tag {
            font-size: 0.7rem; padding: 0.15rem 0.5rem; border-radius: 999px;
            background: rgba(99,102,241,0.2); color: #818cf8; font-weight: 600;
        }
        .formula-card p {
            color: #94a3b8; font-size: 0.9rem; line-height: 1.6; margin: 0 0 0.85rem;
        }
        .formula-card .fc-syntax {
            background: rgba(15,23,42,0.8); border-radius: 0.5rem;
            padding: 0.6rem 0.9rem; font-family: 'Courier New', monospace;
            font-size: 0.82rem; color: #86efac; margin-bottom: 0.6rem;
            border: 1px solid rgba(255,255,255,0.06);
        }
        .formula-card .fc-example {
            font-size: 0.82rem; color: #64748b;
        }
        .formula-card .fc-example code {
            background: rgba(15,23,42,0.6); border-radius: 4px;
            padding: 0.1rem 0.4rem; font-size: 0.8rem; color: #fbbf24;
        }

        .guide-tip {
            background: rgba(34,197,94,0.07);
            border-left: 3px solid #22c55e;
            border-radius: 0 0.75rem 0.75rem 0;
            padding: 1rem 1.25rem;
            margin: 1.5rem 0;
            font-size: 0.92rem; color: #94a3b8; line-height: 1.65;
        }
        .guide-tip strong { color: #22c55e; }

        .guide-table-wrap { overflow-x: auto; margin: 1.25rem 0; }
        .guide-table {
            width: 100%; border-collapse: collapse; font-size: 0.88rem;
        }
        .guide-table th {
            background: rgba(99,102,241,0.12); color: #a5b4fc;
            padding: 0.65rem 1rem; text-align: left; font-weight: 700;
            border-bottom: 1px solid rgba(255,255,255,0.08);
        }
        .guide-table td {
            padding: 0.65rem 1rem; border-bottom: 1px solid rgba(255,255,255,0.05);
            color: #cbd5e1;
        }
        .guide-table tr:hover td { background: rgba(255,255,255,0.02); }
        .guide-table code { color: #fbbf24; font-size: 0.82rem; }

        .guide-cta {
            text-align: center;
            padding: 2.5rem 1rem;
            background: linear-gradient(135deg, rgba(99,102,241,0.12), rgba(34,197,94,0.08));
            border-radius: 1.25rem;
            border: 1px solid rgba(99,102,241,0.2);
            margin-top: 3rem;
        }
        .guide-cta h2 { font-size: 1.5rem; font-weight: 900; margin: 0 0 0.5rem; }
        .guide-cta p { color: #94a3b8; margin: 0 0 1.5rem; }

        @media (max-width: 600px) {
            .guide-formula-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body class="app-page">
    <div class="page-shell">
        <!-- Header -->
        <header class="site-header site-header--landing" data-reveal>
            <a class="brand" href="index.php">
                <span class="brand__mark"><img src="assets/img/logo.png" alt="Excel Snake" width="46" height="46"></span>
                <span>
                    <strong>Excel Snake</strong>
                    <small>Aprende jugando</small>
                </span>
            </a>
            <nav class="site-nav" id="main-nav">
                <a href="index.php">Inicio</a>
                <a href="leaderboard.php">Ranking</a>
                <a href="guia-excel.php" aria-current="page">Guía Excel</a>
            </nav>
            <button class="nav-toggle" type="button" aria-label="Menú" aria-expanded="false" data-nav-toggle>
                <span class="nav-toggle__bar"></span>
                <span class="nav-toggle__bar"></span>
                <span class="nav-toggle__bar"></span>
            </button>
        </header>

        <!-- Hero -->
        <div class="guide-hero" data-reveal>
            <span class="eyebrow"><i class="fa-solid fa-book-open"></i> Guía gratuita</span>
            <h1>Fórmulas de Excel explicadas</h1>
            <p>Desde las funciones básicas hasta las combinaciones avanzadas. Aprende la sintaxis, para qué sirve cada fórmula y practica con ejemplos reales.</p>
        </div>

        <!-- Table of contents -->
        <div class="guide-toc" data-reveal>
            <h2><i class="fa-solid fa-list"></i> Contenidos</h2>
            <ol>
                <li><a href="#basicas">Funciones básicas: SUMA, PROMEDIO, CONTAR</a></li>
                <li><a href="#logicas">Funciones lógicas: SI, Y, O, SI.ERROR</a></li>
                <li><a href="#busqueda">Búsqueda y referencia: BUSCARV, BUSCARX, INDICE+COINCIDIR</a></li>
                <li><a href="#condicionales">Funciones condicionales: SUMAR.SI, CONTAR.SI, PROMEDIO.SI</a></li>
                <li><a href="#texto">Texto: CONCATENAR, IZQUIERDA, DERECHA, LARGO, MINUSC</a></li>
                <li><a href="#fechas">Fechas: HOY, AHORA, AÑO, MES, DIA, SIFECHA</a></li>
                <li><a href="#matematicas">Matemáticas: ENTERO, REDONDEAR, RESIDUO, POTENCIA</a></li>
                <li><a href="#comparativa">Tabla comparativa rápida</a></li>
            </ol>
        </div>

        <main class="guide-main">

            <!-- ─── BÁSICAS ─── -->
            <section class="guide-section" id="basicas" data-reveal>
                <div class="guide-section-header">
                    <div class="guide-icon" style="background:rgba(34,197,94,0.15);color:#22c55e">
                        <i class="fa-solid fa-calculator"></i>
                    </div>
                    <h2>Funciones básicas</h2>
                </div>
                <div class="guide-formula-grid">
                    <div class="formula-card">
                        <h3>SUMA <span class="fc-tag">Básico</span></h3>
                        <p>Suma todos los valores de un rango de celdas. Es la función más usada en Excel.</p>
                        <div class="fc-syntax">=SUMA(rango)</div>
                        <div class="fc-example">Ejemplo: <code>=SUMA(B2:B10)</code> — suma los valores de B2 hasta B10.</div>
                    </div>
                    <div class="formula-card">
                        <h3>PROMEDIO <span class="fc-tag">Básico</span></h3>
                        <p>Calcula el promedio aritmético de un conjunto de valores. Ignora celdas vacías.</p>
                        <div class="fc-syntax">=PROMEDIO(rango)</div>
                        <div class="fc-example">Ejemplo: <code>=PROMEDIO(C2:C20)</code> — promedio de las notas del grupo.</div>
                    </div>
                    <div class="formula-card">
                        <h3>CONTAR <span class="fc-tag">Básico</span></h3>
                        <p>Cuenta cuántas celdas de un rango contienen números. No cuenta texto ni vacíos.</p>
                        <div class="fc-syntax">=CONTAR(rango)</div>
                        <div class="fc-example">Ejemplo: <code>=CONTAR(D2:D50)</code> — número de empleados con salario registrado.</div>
                    </div>
                    <div class="formula-card">
                        <h3>CONTARA <span class="fc-tag">Básico</span></h3>
                        <p>Cuenta celdas no vacías, incluyendo texto, fechas y números.</p>
                        <div class="fc-syntax">=CONTARA(rango)</div>
                        <div class="fc-example">Ejemplo: <code>=CONTARA(A2:A100)</code> — total de filas con algún dato.</div>
                    </div>
                    <div class="formula-card">
                        <h3>MAX / MIN <span class="fc-tag">Básico</span></h3>
                        <p>Devuelven el valor máximo o mínimo de un rango, respectivamente.</p>
                        <div class="fc-syntax">=MAX(rango) / =MIN(rango)</div>
                        <div class="fc-example">Ejemplo: <code>=MAX(E2:E30)</code> — la venta más alta del mes.</div>
                    </div>
                    <div class="formula-card">
                        <h3>REDONDEAR <span class="fc-tag">Básico</span></h3>
                        <p>Redondea un número a la cantidad de decimales indicada.</p>
                        <div class="fc-syntax">=REDONDEAR(número, decimales)</div>
                        <div class="fc-example">Ejemplo: <code>=REDONDEAR(C5,2)</code> — redondea a 2 decimales.</div>
                    </div>
                </div>
                <div class="guide-tip">
                    <strong>Consejo:</strong> Puedes combinar rangos no contiguos separándolos con punto y coma:
                    <code style="color:#fbbf24">= SUMA(B2:B10; D2:D10)</code>
                </div>
            </section>

            <!-- ─── LÓGICAS ─── -->
            <section class="guide-section" id="logicas" data-reveal>
                <div class="guide-section-header">
                    <div class="guide-icon" style="background:rgba(99,102,241,0.15);color:#818cf8">
                        <i class="fa-solid fa-code-branch"></i>
                    </div>
                    <h2>Funciones lógicas</h2>
                </div>
                <div class="guide-formula-grid">
                    <div class="formula-card">
                        <h3>SI <span class="fc-tag">Intermedio</span></h3>
                        <p>Evalúa una condición. Devuelve un valor si es verdadera y otro si es falsa.</p>
                        <div class="fc-syntax">=SI(prueba_lógica; valor_si_verdadero; valor_si_falso)</div>
                        <div class="fc-example">Ejemplo: <code>=SI(B2>=60;"Aprobado";"Reprobado")</code></div>
                    </div>
                    <div class="formula-card">
                        <h3>Y / O <span class="fc-tag">Intermedio</span></h3>
                        <p><strong>Y</strong> devuelve VERDADERO solo si todas las condiciones se cumplen. <strong>O</strong> devuelve VERDADERO si al menos una se cumple.</p>
                        <div class="fc-syntax">=Y(cond1; cond2) / =O(cond1; cond2)</div>
                        <div class="fc-example">Ejemplo: <code>=SI(Y(B2>0;C2="Norte");"Sí";"No")</code></div>
                    </div>
                    <div class="formula-card">
                        <h3>SI.ERROR <span class="fc-tag">Intermedio</span></h3>
                        <p>Devuelve un valor alternativo cuando una fórmula genera un error como #N/A, #DIV/0! o #VALOR!.</p>
                        <div class="fc-syntax">=SI.ERROR(fórmula; valor_si_error)</div>
                        <div class="fc-example">Ejemplo: <code>=SI.ERROR(BUSCARV(A2;tabla;2;0);"")</code></div>
                    </div>
                    <div class="formula-card">
                        <h3>SI anidado <span class="fc-tag">Avanzado</span></h3>
                        <p>Permite evaluar múltiples condiciones encadenando funciones SI una dentro de otra.</p>
                        <div class="fc-syntax">=SI(c1; v1; SI(c2; v2; v3))</div>
                        <div class="fc-example">Ejemplo: <code>=SI(B2>=90;"A";SI(B2>=70;"B";"C"))</code></div>
                    </div>
                    <div class="formula-card">
                        <h3>IFS (SI.CONJUNTO) <span class="fc-tag">Avanzado</span></h3>
                        <p>Evalúa múltiples condiciones en orden, sin necesidad de anidar funciones SI.</p>
                        <div class="fc-syntax">=IFS(cond1;val1; cond2;val2; ...)</div>
                        <div class="fc-example">Ejemplo: <code>=IFS(B2>=90;"Excelente";B2>=60;"Aprobado";VERDADERO;"Reprobado")</code></div>
                    </div>
                    <div class="formula-card">
                        <h3>NO <span class="fc-tag">Básico</span></h3>
                        <p>Invierte el resultado lógico: convierte VERDADERO en FALSO y viceversa.</p>
                        <div class="fc-syntax">=NO(condición)</div>
                        <div class="fc-example">Ejemplo: <code>=SI(NO(ESBLANCO(A2));"Tiene dato";"")</code></div>
                    </div>
                </div>
            </section>

            <!-- ─── BÚSQUEDA ─── -->
            <section class="guide-section" id="busqueda" data-reveal>
                <div class="guide-section-header">
                    <div class="guide-icon" style="background:rgba(251,191,36,0.12);color:#fbbf24">
                        <i class="fa-solid fa-magnifying-glass"></i>
                    </div>
                    <h2>Búsqueda y referencia</h2>
                </div>
                <div class="guide-formula-grid">
                    <div class="formula-card">
                        <h3>BUSCARV <span class="fc-tag">Intermedio</span></h3>
                        <p>Busca un valor en la primera columna de una tabla y devuelve el dato de la columna indicada. Una de las funciones más usadas en el trabajo.</p>
                        <div class="fc-syntax">=BUSCARV(valor; tabla; col; [exacto])</div>
                        <div class="fc-example">Ejemplo: <code>=BUSCARV(A2;$D$2:$F$50;2;0)</code> — busca exacto y devuelve columna 2.</div>
                    </div>
                    <div class="formula-card">
                        <h3>BUSCARH <span class="fc-tag">Intermedio</span></h3>
                        <p>Como BUSCARV pero busca en filas en lugar de columnas. Útil cuando los encabezados están en la primera fila.</p>
                        <div class="fc-syntax">=BUSCARH(valor; tabla; fila; [exacto])</div>
                        <div class="fc-example">Ejemplo: <code>=BUSCARH("Enero";A1:M2;2;0)</code></div>
                    </div>
                    <div class="formula-card">
                        <h3>BUSCARX <span class="fc-tag">Avanzado</span></h3>
                        <p>Versión moderna y más flexible que BUSCARV: puede buscar en cualquier dirección y devuelve rangos completos.</p>
                        <div class="fc-syntax">=BUSCARX(valor; rango_búsqueda; rango_resultado)</div>
                        <div class="fc-example">Ejemplo: <code>=BUSCARX(A2;D2:D50;E2:E50)</code></div>
                    </div>
                    <div class="formula-card">
                        <h3>INDICE + COINCIDIR <span class="fc-tag">Avanzado</span></h3>
                        <p>Combinación poderosa que supera a BUSCARV: puede buscar en cualquier columna y devuelve datos de cualquier dirección.</p>
                        <div class="fc-syntax">=INDICE(col_resultado; COINCIDIR(valor; col_búsqueda; 0))</div>
                        <div class="fc-example">Ejemplo: <code>=INDICE(C2:C50;COINCIDIR(A2;B2:B50;0))</code></div>
                    </div>
                    <div class="formula-card">
                        <h3>COINCIDIR <span class="fc-tag">Intermedio</span></h3>
                        <p>Devuelve la posición relativa de un valor dentro de un rango. Muy útil combinada con INDICE.</p>
                        <div class="fc-syntax">=COINCIDIR(valor; rango; tipo)</div>
                        <div class="fc-example">Ejemplo: <code>=COINCIDIR("Juan";A2:A30;0)</code> — posición exacta de "Juan".</div>
                    </div>
                    <div class="formula-card">
                        <h3>DESREF <span class="fc-tag">Avanzado</span></h3>
                        <p>Devuelve una referencia desplazada desde una celda base, permitiendo rangos dinámicos.</p>
                        <div class="fc-syntax">=DESREF(ref; filas; cols; [alto]; [ancho])</div>
                        <div class="fc-example">Ejemplo: <code>=DESREF(A1;2;1)</code> — celda B3.</div>
                    </div>
                </div>
                <div class="guide-tip">
                    <strong>BUSCARV vs BUSCARX:</strong> Si tu versión de Excel es 2019 o anterior usa BUSCARV. Si tienes Microsoft 365 o Excel 2021, prefiere BUSCARX: es más flexible, permite búsqueda inversa y no necesita que la columna clave sea la primera.
                </div>
            </section>

            <!-- ─── CONDICIONALES ─── -->
            <section class="guide-section" id="condicionales" data-reveal>
                <div class="guide-section-header">
                    <div class="guide-icon" style="background:rgba(239,68,68,0.12);color:#f87171">
                        <i class="fa-solid fa-filter"></i>
                    </div>
                    <h2>Funciones condicionales</h2>
                </div>
                <div class="guide-formula-grid">
                    <div class="formula-card">
                        <h3>SUMAR.SI <span class="fc-tag">Intermedio</span></h3>
                        <p>Suma los valores de un rango solo cuando se cumple una condición.</p>
                        <div class="fc-syntax">=SUMAR.SI(rango_criterio; criterio; rango_suma)</div>
                        <div class="fc-example">Ejemplo: <code>=SUMAR.SI(A2:A20;"Norte";B2:B20)</code> — suma ventas de la región Norte.</div>
                    </div>
                    <div class="formula-card">
                        <h3>SUMAR.SI.CONJUNTO <span class="fc-tag">Avanzado</span></h3>
                        <p>Suma con múltiples criterios simultáneos. Todos los criterios deben cumplirse.</p>
                        <div class="fc-syntax">=SUMAR.SI.CONJUNTO(suma; rng1; crit1; rng2; crit2)</div>
                        <div class="fc-example">Ejemplo: <code>=SUMAR.SI.CONJUNTO(C2:C20;A2:A20;"Norte";B2:B20;"Laptop")</code></div>
                    </div>
                    <div class="formula-card">
                        <h3>CONTAR.SI <span class="fc-tag">Intermedio</span></h3>
                        <p>Cuenta las celdas que cumplen un criterio determinado.</p>
                        <div class="fc-syntax">=CONTAR.SI(rango; criterio)</div>
                        <div class="fc-example">Ejemplo: <code>=CONTAR.SI(D2:D50;"Aprobado")</code></div>
                    </div>
                    <div class="formula-card">
                        <h3>CONTAR.SI.CONJUNTO <span class="fc-tag">Avanzado</span></h3>
                        <p>Cuenta las celdas que cumplen múltiples condiciones al mismo tiempo.</p>
                        <div class="fc-syntax">=CONTAR.SI.CONJUNTO(rng1; crit1; rng2; crit2)</div>
                        <div class="fc-example">Ejemplo: <code>=CONTAR.SI.CONJUNTO(A2:A50;"Norte";C2:C50;">1000")</code></div>
                    </div>
                    <div class="formula-card">
                        <h3>PROMEDIO.SI <span class="fc-tag">Intermedio</span></h3>
                        <p>Calcula el promedio de los valores que cumplen una condición.</p>
                        <div class="fc-syntax">=PROMEDIO.SI(rango; criterio; rango_promedio)</div>
                        <div class="fc-example">Ejemplo: <code>=PROMEDIO.SI(B2:B30;">=60";C2:C30)</code></div>
                    </div>
                    <div class="formula-card">
                        <h3>SUMAPRODUCTO <span class="fc-tag">Avanzado</span></h3>
                        <p>Multiplica rangos elemento a elemento y suma los productos. Muy versátil para totales ponderados y conteos con múltiples condiciones.</p>
                        <div class="fc-syntax">=SUMAPRODUCTO(array1; array2)</div>
                        <div class="fc-example">Ejemplo: <code>=SUMAPRODUCTO(B2:B10;C2:C10)</code> — suma de precio × cantidad.</div>
                    </div>
                </div>
            </section>

            <!-- ─── TEXTO ─── -->
            <section class="guide-section" id="texto" data-reveal>
                <div class="guide-section-header">
                    <div class="guide-icon" style="background:rgba(168,85,247,0.12);color:#c084fc">
                        <i class="fa-solid fa-font"></i>
                    </div>
                    <h2>Funciones de texto</h2>
                </div>
                <div class="guide-formula-grid">
                    <div class="formula-card">
                        <h3>CONCATENAR / &amp; <span class="fc-tag">Básico</span></h3>
                        <p>Une varios textos en uno. El operador <code>&amp;</code> es la forma más rápida y común.</p>
                        <div class="fc-syntax">=CONCATENAR(txt1; txt2) o =A2&amp;" "&amp;B2</div>
                        <div class="fc-example">Ejemplo: <code>=A2&amp;" "&amp;B2</code> — une nombre y apellido con espacio.</div>
                    </div>
                    <div class="formula-card">
                        <h3>IZQUIERDA / DERECHA <span class="fc-tag">Básico</span></h3>
                        <p>Extrae N caracteres desde el inicio o el final de un texto.</p>
                        <div class="fc-syntax">=IZQUIERDA(texto; N) / =DERECHA(texto; N)</div>
                        <div class="fc-example">Ejemplo: <code>=IZQUIERDA(A2;3)</code> — primeras 3 letras del texto.</div>
                    </div>
                    <div class="formula-card">
                        <h3>EXTRAE <span class="fc-tag">Intermedio</span></h3>
                        <p>Extrae un fragmento de texto desde una posición específica.</p>
                        <div class="fc-syntax">=EXTRAE(texto; inicio; cant_caracteres)</div>
                        <div class="fc-example">Ejemplo: <code>=EXTRAE(A2;5;4)</code> — 4 caracteres desde la posición 5.</div>
                    </div>
                    <div class="formula-card">
                        <h3>LARGO <span class="fc-tag">Básico</span></h3>
                        <p>Devuelve la cantidad de caracteres de un texto, incluyendo espacios.</p>
                        <div class="fc-syntax">=LARGO(texto)</div>
                        <div class="fc-example">Ejemplo: <code>=LARGO(A2)</code> — longitud del contenido de A2.</div>
                    </div>
                    <div class="formula-card">
                        <h3>MAYUSC / MINUSC / NOMPROPIO <span class="fc-tag">Básico</span></h3>
                        <p>Convierten texto a mayúsculas, minúsculas o formato de nombre propio.</p>
                        <div class="fc-syntax">=MAYUSC(texto) / =MINUSC(texto) / =NOMPROPIO(texto)</div>
                        <div class="fc-example">Ejemplo: <code>=NOMPROPIO("juan pérez")</code> → Juan Pérez</div>
                    </div>
                    <div class="formula-card">
                        <h3>SUSTITUIR / REEMPLAZAR <span class="fc-tag">Intermedio</span></h3>
                        <p><strong>SUSTITUIR</strong> reemplaza texto específico. <strong>REEMPLAZAR</strong> reemplaza por posición.</p>
                        <div class="fc-syntax">=SUSTITUIR(texto; buscar; reemplazar)</div>
                        <div class="fc-example">Ejemplo: <code>=SUSTITUIR(A2;"-";"/")</code> — reemplaza guiones por barras.</div>
                    </div>
                    <div class="formula-card">
                        <h3>TEXTO <span class="fc-tag">Intermedio</span></h3>
                        <p>Formatea un número o fecha como texto con el formato indicado.</p>
                        <div class="fc-syntax">=TEXTO(valor; "formato")</div>
                        <div class="fc-example">Ejemplo: <code>=TEXTO(A2;"dd/mm/yyyy")</code> — fecha en formato día/mes/año.</div>
                    </div>
                    <div class="formula-card">
                        <h3>HALLAR / ENCONTRAR <span class="fc-tag">Intermedio</span></h3>
                        <p>Devuelven la posición de un texto dentro de otro. HALLAR no distingue mayúsculas; ENCONTRAR sí.</p>
                        <div class="fc-syntax">=HALLAR(buscar; en_texto; [inicio])</div>
                        <div class="fc-example">Ejemplo: <code>=HALLAR("@";A2)</code> — posición del @ en un email.</div>
                    </div>
                </div>
            </section>

            <!-- ─── FECHAS ─── -->
            <section class="guide-section" id="fechas" data-reveal>
                <div class="guide-section-header">
                    <div class="guide-icon" style="background:rgba(6,182,212,0.12);color:#22d3ee">
                        <i class="fa-solid fa-calendar-days"></i>
                    </div>
                    <h2>Funciones de fecha y hora</h2>
                </div>
                <div class="guide-formula-grid">
                    <div class="formula-card">
                        <h3>HOY / AHORA <span class="fc-tag">Básico</span></h3>
                        <p><strong>HOY()</strong> devuelve la fecha actual. <strong>AHORA()</strong> devuelve fecha y hora. Se actualizan automáticamente.</p>
                        <div class="fc-syntax">=HOY() / =AHORA()</div>
                        <div class="fc-example">Ejemplo: <code>=HOY()-A2</code> — días transcurridos desde la fecha en A2.</div>
                    </div>
                    <div class="formula-card">
                        <h3>AÑO / MES / DIA <span class="fc-tag">Básico</span></h3>
                        <p>Extraen el año, mes o día de una fecha respectivamente.</p>
                        <div class="fc-syntax">=AÑO(fecha) / =MES(fecha) / =DIA(fecha)</div>
                        <div class="fc-example">Ejemplo: <code>=AÑO(A2)</code> — año de la fecha en A2.</div>
                    </div>
                    <div class="formula-card">
                        <h3>FECHA <span class="fc-tag">Básico</span></h3>
                        <p>Construye una fecha a partir de año, mes y día separados.</p>
                        <div class="fc-syntax">=FECHA(año; mes; día)</div>
                        <div class="fc-example">Ejemplo: <code>=FECHA(2025;1;31)</code> → 31/01/2025</div>
                    </div>
                    <div class="formula-card">
                        <h3>SIFECHA <span class="fc-tag">Intermedio</span></h3>
                        <p>Calcula la diferencia entre dos fechas en años, meses o días. Función no documentada pero muy útil.</p>
                        <div class="fc-syntax">=SIFECHA(inicio; fin; unidad)</div>
                        <div class="fc-example">Ejemplo: <code>=SIFECHA(A2;HOY();"Y")</code> — años de antigüedad.</div>
                    </div>
                    <div class="formula-card">
                        <h3>DIA.LAB / DIA.LAB.INTL <span class="fc-tag">Intermedio</span></h3>
                        <p>Calcula una fecha sumando N días laborables, excluyendo fines de semana y festivos.</p>
                        <div class="fc-syntax">=DIA.LAB(inicio; días; [festivos])</div>
                        <div class="fc-example">Ejemplo: <code>=DIA.LAB(HOY();10)</code> — fecha en 10 días hábiles.</div>
                    </div>
                    <div class="formula-card">
                        <h3>FIN.MES <span class="fc-tag">Intermedio</span></h3>
                        <p>Devuelve el último día del mes para una fecha dada, desplazando N meses hacia adelante o atrás.</p>
                        <div class="fc-syntax">=FIN.MES(fecha; meses)</div>
                        <div class="fc-example">Ejemplo: <code>=FIN.MES(A2;0)</code> — último día del mes de A2.</div>
                    </div>
                </div>
            </section>

            <!-- ─── MATEMÁTICAS ─── -->
            <section class="guide-section" id="matematicas" data-reveal>
                <div class="guide-section-header">
                    <div class="guide-icon" style="background:rgba(249,115,22,0.12);color:#fb923c">
                        <i class="fa-solid fa-square-root-variable"></i>
                    </div>
                    <h2>Funciones matemáticas</h2>
                </div>
                <div class="guide-formula-grid">
                    <div class="formula-card">
                        <h3>ENTERO / TRUNCAR <span class="fc-tag">Básico</span></h3>
                        <p><strong>ENTERO</strong> redondea hacia abajo al entero más cercano. <strong>TRUNCAR</strong> simplemente elimina los decimales.</p>
                        <div class="fc-syntax">=ENTERO(número) / =TRUNCAR(número; decimales)</div>
                        <div class="fc-example">Ejemplo: <code>=ENTERO(4.9)</code> → 4 · <code>=TRUNCAR(4.987;1)</code> → 4.9</div>
                    </div>
                    <div class="formula-card">
                        <h3>RESIDUO <span class="fc-tag">Intermedio</span></h3>
                        <p>Devuelve el resto de una división. Muy útil para detectar pares/impares o ciclos.</p>
                        <div class="fc-syntax">=RESIDUO(número; divisor)</div>
                        <div class="fc-example">Ejemplo: <code>=SI(RESIDUO(A2;2)=0;"Par";"Impar")</code></div>
                    </div>
                    <div class="formula-card">
                        <h3>POTENCIA / RAIZ <span class="fc-tag">Básico</span></h3>
                        <p><strong>POTENCIA</strong> eleva un número a una potencia. <strong>RAIZ</strong> calcula la raíz cuadrada.</p>
                        <div class="fc-syntax">=POTENCIA(base; exponente) / =RAIZ(número)</div>
                        <div class="fc-example">Ejemplo: <code>=POTENCIA(2;10)</code> → 1024</div>
                    </div>
                    <div class="formula-card">
                        <h3>ABS <span class="fc-tag">Básico</span></h3>
                        <p>Devuelve el valor absoluto de un número (sin signo negativo).</p>
                        <div class="fc-syntax">=ABS(número)</div>
                        <div class="fc-example">Ejemplo: <code>=ABS(-150)</code> → 150</div>
                    </div>
                    <div class="formula-card">
                        <h3>ALEATORIO.ENTRE <span class="fc-tag">Básico</span></h3>
                        <p>Genera un número entero aleatorio entre un mínimo y un máximo.</p>
                        <div class="fc-syntax">=ALEATORIO.ENTRE(mín; máx)</div>
                        <div class="fc-example">Ejemplo: <code>=ALEATORIO.ENTRE(1;100)</code></div>
                    </div>
                    <div class="formula-card">
                        <h3>K.ESIMO.MAYOR / MENOR <span class="fc-tag">Intermedio</span></h3>
                        <p>Devuelven el k-ésimo valor más grande o más pequeño de un rango.</p>
                        <div class="fc-syntax">=K.ESIMO.MAYOR(rango; k)</div>
                        <div class="fc-example">Ejemplo: <code>=K.ESIMO.MAYOR(B2:B50;3)</code> — tercer valor más alto.</div>
                    </div>
                </div>
            </section>

            <!-- ─── TABLA COMPARATIVA ─── -->
            <section class="guide-section" id="comparativa" data-reveal>
                <div class="guide-section-header">
                    <div class="guide-icon" style="background:rgba(34,197,94,0.12);color:#4ade80">
                        <i class="fa-solid fa-table"></i>
                    </div>
                    <h2>Tabla comparativa rápida</h2>
                </div>
                <div class="guide-table-wrap">
                    <table class="guide-table">
                        <thead>
                            <tr>
                                <th>Función</th>
                                <th>Qué hace</th>
                                <th>Nivel</th>
                                <th>Ejemplo</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr><td><code>SUMA</code></td><td>Suma un rango</td><td>Básico</td><td><code>=SUMA(A1:A10)</code></td></tr>
                            <tr><td><code>PROMEDIO</code></td><td>Promedio de un rango</td><td>Básico</td><td><code>=PROMEDIO(B1:B20)</code></td></tr>
                            <tr><td><code>CONTAR</code></td><td>Cuenta celdas con números</td><td>Básico</td><td><code>=CONTAR(C1:C50)</code></td></tr>
                            <tr><td><code>SI</code></td><td>Condición verdadero/falso</td><td>Intermedio</td><td><code>=SI(A1>10;"Sí";"No")</code></td></tr>
                            <tr><td><code>SUMAR.SI</code></td><td>Suma con criterio</td><td>Intermedio</td><td><code>=SUMAR.SI(A:A;"Norte";B:B)</code></td></tr>
                            <tr><td><code>CONTAR.SI</code></td><td>Cuenta con criterio</td><td>Intermedio</td><td><code>=CONTAR.SI(A:A;"Sí")</code></td></tr>
                            <tr><td><code>BUSCARV</code></td><td>Busca en primera columna</td><td>Intermedio</td><td><code>=BUSCARV(A2;Tabla;2;0)</code></td></tr>
                            <tr><td><code>SI.ERROR</code></td><td>Captura errores</td><td>Intermedio</td><td><code>=SI.ERROR(BUSCARV(...);"—")</code></td></tr>
                            <tr><td><code>SUMAR.SI.CONJUNTO</code></td><td>Suma con múltiples criterios</td><td>Avanzado</td><td><code>=SUMAR.SI.CONJUNTO(C:C;A:A;"Norte";B:B;"PC")</code></td></tr>
                            <tr><td><code>BUSCARX</code></td><td>Búsqueda flexible</td><td>Avanzado</td><td><code>=BUSCARX(A2;D:D;E:E)</code></td></tr>
                            <tr><td><code>INDICE+COINCIDIR</code></td><td>Búsqueda en cualquier dirección</td><td>Avanzado</td><td><code>=INDICE(C:C;COINCIDIR(A2;B:B;0))</code></td></tr>
                            <tr><td><code>SIFECHA</code></td><td>Diferencia entre fechas</td><td>Intermedio</td><td><code>=SIFECHA(A2;HOY();"Y")</code></td></tr>
                            <tr><td><code>SUMAPRODUCTO</code></td><td>Suma de productos</td><td>Avanzado</td><td><code>=SUMAPRODUCTO(B:B;C:C)</code></td></tr>
                        </tbody>
                    </table>
                </div>
            </section>

            <!-- CTA -->
            <div class="guide-cta" data-reveal>
                <h2><i class="fa-solid fa-gamepad" style="color:#22c55e"></i> ¿Listo para practicar?</h2>
                <p>Pon a prueba todo lo que aprendiste con 200 niveles interactivos. La serpiente, las fórmulas y el ranking te esperan.</p>
                <a class="button button--primary button--glow" href="index.php#acceso">
                    <i class="fa-solid fa-play"></i> Empezar a jugar gratis
                </a>
            </div>

        </main>

        <footer class="landing-footer">
            <div class="landing-footer__brand">
                <img src="assets/img/logo.png" alt="Excel Snake" width="32" height="32">
                <span>Excel Snake</span>
            </div>
            <div class="landing-footer__links">
                <a href="privacy.php">Privacidad</a>
                <a href="cookies.php">Cookies</a>
                <a href="guia-excel.php">Guía Excel</a>
                <a href="leaderboard.php">Ranking</a>
            </div>
            <p class="landing-footer__copy">&copy; <?= date('Y') ?> Excel Snake. Aprende jugando.</p>
        </footer>
    </div>
    <?php render_app_scripts(); ?>
</body>
</html>

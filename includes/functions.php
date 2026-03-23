<?php
declare(strict_types=1);

require_once __DIR__ . '/db.php';

function redirect(string $path): void
{
    header('Location: ' . $path);
    exit;
}

function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function set_flash(string $type, string $message): void
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function get_flash(): ?array
{
    if (!isset($_SESSION['flash'])) {
        return null;
    }

    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);

    return $flash;
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function verify_csrf(?string $token): bool
{
    return is_string($token)
        && isset($_SESSION['csrf_token'])
        && hash_equals($_SESSION['csrf_token'], $token);
}

function is_logged_in(): bool
{
    return isset($_SESSION['user_id']);
}

function current_user_id(): ?int
{
    return isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : null;
}

function require_login(): void
{
    if (!is_logged_in()) {
        set_flash('error', 'Debes iniciar sesión para continuar.');
        redirect('index.php');
    }

    $user = fetch_user_by_id((int) $_SESSION['user_id']);
    if (!$user) {
        session_destroy();
        session_start();
        set_flash('error', 'Tu sesión ha expirado. Inicia sesión de nuevo.');
        redirect('index.php');
    }
}

function fetch_user_by_login(string $login): ?array
{
    $stmt = getPDO()->prepare('SELECT * FROM users WHERE email = :email_login OR username = :username_login LIMIT 1');
    $stmt->execute([
        'email_login' => $login,
        'username_login' => $login,
    ]);
    $user = $stmt->fetch();

    return $user ?: null;
}

function fetch_user_by_id(int $userId): ?array
{
    $stmt = getPDO()->prepare('SELECT id, username, email, created_at FROM users WHERE id = ? LIMIT 1');
    $stmt->execute([$userId]);
    $user = $stmt->fetch();

    return $user ?: null;
}

function initialize_progress(int $userId): void
{
    $stmt = getPDO()->prepare(
        'INSERT INTO progress (user_id, nivel_actual, puntos, vidas, racha_actual, niveles_completados)
         VALUES (?, 1, 0, 5, 0, 0)
         ON DUPLICATE KEY UPDATE user_id = user_id'
    );
    $stmt->execute([$userId]);
}

function is_user_vip(int $userId): bool
{
    $stmt = getPDO()->prepare('SELECT is_vip FROM users WHERE id = ? LIMIT 1');
    $stmt->execute([$userId]);
    $row = $stmt->fetch();
    return $row && (int) $row['is_vip'] === 1;
}

function regenerate_lives(int $userId): void
{
    $pdo = getPDO();
    $stmt = $pdo->prepare('SELECT vidas, last_life_lost_at FROM progress WHERE user_id = ? LIMIT 1');
    $stmt->execute([$userId]);
    $row = $stmt->fetch();

    if (!$row || (int) $row['vidas'] >= 5 || $row['last_life_lost_at'] === null) {
        return;
    }

    $lostAt = new DateTimeImmutable($row['last_life_lost_at']);
    $now = new DateTimeImmutable();
    $elapsedMinutes = (int) floor(($now->getTimestamp() - $lostAt->getTimestamp()) / 60);
    $livesToAdd = (int) floor($elapsedMinutes / 15);

    if ($livesToAdd <= 0) {
        return;
    }

    $currentLives = (int) $row['vidas'];
    $newLives = min(5, $currentLives + $livesToAdd);
    $minutesUsed = $livesToAdd * 15;
    $newLostAt = $newLives >= 5 ? null : $lostAt->modify("+{$minutesUsed} minutes")->format('Y-m-d H:i:s');

    $update = $pdo->prepare('UPDATE progress SET vidas = ?, last_life_lost_at = ? WHERE user_id = ?');
    $update->execute([$newLives, $newLostAt, $userId]);
}

function get_user_progress(int $userId): array
{
    initialize_progress($userId);

    if (!is_user_vip($userId)) {
        regenerate_lives($userId);
    }

    $stmt = getPDO()->prepare('SELECT * FROM progress WHERE user_id = ? LIMIT 1');
    $stmt->execute([$userId]);
    $progress = $stmt->fetch();

    if (!$progress) {
        return [
            'nivel_actual' => 1,
            'puntos' => 0,
            'vidas' => 5,
            'racha_actual' => 0,
            'niveles_completados' => 0,
            'last_life_lost_at' => null,
        ];
    }

    return $progress;
}

function get_all_levels(): array
{
    $stmt = getPDO()->query('SELECT * FROM levels ORDER BY numero ASC');
    return $stmt->fetchAll();
}

function get_level_by_number(int $number): ?array
{
    $stmt = getPDO()->prepare('SELECT * FROM levels WHERE numero = ? LIMIT 1');
    $stmt->execute([$number]);
    $level = $stmt->fetch();

    return $level ?: null;
}

function get_level_by_id(int $levelId): ?array
{
    $stmt = getPDO()->prepare('SELECT * FROM levels WHERE id = ? LIMIT 1');
    $stmt->execute([$levelId]);
    $level = $stmt->fetch();

    return $level ?: null;
}

function get_user_level_status_map(int $userId): array
{
    $stmt = getPDO()->prepare(
        'SELECT l.numero, uls.completed_at, uls.attempts, uls.score_earned
         FROM user_level_status uls
         INNER JOIN levels l ON l.id = uls.level_id
         WHERE uls.user_id = ?'
    );
    $stmt->execute([$userId]);

    $statusMap = [];
    foreach ($stmt->fetchAll() as $row) {
        $statusMap[(int) $row['numero']] = $row;
    }

    return $statusMap;
}

function get_single_level_status(int $userId, int $levelId): ?array
{
    $stmt = getPDO()->prepare('SELECT * FROM user_level_status WHERE user_id = ? AND level_id = ? LIMIT 1');
    $stmt->execute([$userId, $levelId]);
    $status = $stmt->fetch();

    return $status ?: null;
}

function progress_percentage(array $progress): float
{
    $completed = (int) ($progress['niveles_completados'] ?? 0);
    return min(100, ($completed / TOTAL_LEVELS) * 100);
}

function current_level_percentage(array $progress): float
{
    $current = max(1, min(TOTAL_LEVELS, (int) ($progress['nivel_actual'] ?? 1)));
    return ($current / TOTAL_LEVELS) * 100;
}

function normalize_formula(string $formula): string
{
    $normalized = mb_strtolower(trim($formula), 'UTF-8');
    $normalized = preg_replace('/\s+/', '', $normalized) ?? $normalized;
    $normalized = str_replace(['；', ';'], ',', $normalized);
    $normalized = str_replace('$', '', $normalized);

    // Accept English function names (order matters: longer names first)
    $normalized = str_replace(
        ['averageif(', 'sumif(', 'countif(', 'iferror(', 'vlookup(', 'xlookup(', 'average(', 'count(', 'product(', 'sum(', 'if(', 'false', 'true'],
        ['promedio.si(', 'sumar.si(', 'contar.si(', 'si.error(', 'buscarv(', 'buscarx(', 'promedio(', 'contar(', 'producto(', 'suma(', 'si(', 'falso', 'verdadero'],
        $normalized
    );

    if ($normalized !== '' && $normalized[0] !== '=') {
        $normalized = '=' . $normalized;
    }

    return $normalized;
}

function accepted_formulas(array $level): array
{
    $variants = [(string) $level['respuesta_correcta']];

    if (!empty($level['respuestas_alternativas'])) {
        foreach (explode('||', (string) $level['respuestas_alternativas']) as $variant) {
            $variants[] = $variant;
        }
    }

    return array_values(array_filter(array_map('trim', $variants)));
}

function is_formula_correct(string $formula, array $level): bool
{
    $normalizedUserFormula = normalize_formula($formula);

    foreach (accepted_formulas($level) as $acceptedFormula) {
        if ($normalizedUserFormula === normalize_formula($acceptedFormula)) {
            return true;
        }
    }

    return false;
}

function difficulty_class(string $difficulty): string
{
    return match ($difficulty) {
        'Básico' => 'difficulty-basic',
        'Intermedio 1' => 'difficulty-mid-1',
        'Intermedio 2' => 'difficulty-mid-2',
        'Avanzado 1' => 'difficulty-adv-1',
        default => 'difficulty-adv-2',
    };
}

function motivational_message(bool $correct): string
{
    $positive = [
        'Excelente. Tu fórmula quedó impecable.',
        'Buen trabajo. Ya dominaste este reto.',
        'Nivel superado. Sigues avanzando con ritmo fuerte.',
        'Perfecto. Tu lógica en Excel va mejorando.',
    ];

    $negative = [
        'Casi. Revisa los rangos y vuelve a intentarlo.',
        'No pasa nada. Ajusta la sintaxis y prueba otra vez.',
        'Observa la celda objetivo y valida los argumentos.',
        'Sigue intentándolo. Estás a un paso de resolverlo.',
    ];

    $pool = $correct ? $positive : $negative;
    return $pool[array_rand($pool)];
}

function level_is_unlocked(array $progress, int $levelNumber): bool
{
    return $levelNumber <= (int) $progress['nivel_actual'];
}

function fetch_leaderboard(int $limit = 15): array
{
    $stmt = getPDO()->prepare(
        'SELECT u.username, p.puntos, p.niveles_completados, p.nivel_actual
         FROM progress p
         INNER JOIN users u ON u.id = p.user_id
         ORDER BY p.puntos DESC, p.niveles_completados DESC, p.updated_at ASC
         LIMIT ?'
    );
    $stmt->bindValue(1, $limit, PDO::PARAM_INT);
    $stmt->execute();

    return $stmt->fetchAll();
}

function level_band_title(int $number): string
{
    return match (true) {
        $number <= 20 => 'Básico',
        $number <= 40 => 'Intermedio 1',
        $number <= 60 => 'Intermedio 2',
        $number <= 80 => 'Avanzado 1',
        default => 'Avanzado 2',
    };
}

function level_learning_guide(array $level): array
{
    $formula = mb_strtoupper(normalize_formula((string) ($level['respuesta_correcta'] ?? '')), 'UTF-8');
    $category = mb_strtoupper((string) ($level['categoria'] ?? ''), 'UTF-8');

    if (str_contains($formula, 'SI.ERROR')) {
        return [
            'title' => 'Explicacion del nivel',
            'explanation' => 'SI.ERROR sirve para mostrar un resultado alternativo cuando una formula puede fallar. Primero se evalua la formula principal y, si devuelve error, Excel muestra el valor de respaldo.',
            'example' => '=SI.ERROR(BUSCARV(H2,A2:D10,3,FALSO),"No encontrado")',
        ];
    }

    if (str_contains($formula, 'BUSCARX')) {
        return [
            'title' => 'Explicacion del nivel',
            'explanation' => 'BUSCARX busca un valor en una columna o fila y devuelve el dato relacionado desde otro rango. Es ideal cuando quieres una busqueda exacta y mas flexible que BUSCARV.',
            'example' => '=BUSCARX(H2,A2:A10,C2:C10,"No encontrado")',
        ];
    }

    if (str_contains($formula, 'BUSCARV')) {
        return [
            'title' => 'Explicacion del nivel',
            'explanation' => 'BUSCARV localiza un valor en la primera columna de una tabla y devuelve el dato de otra columna en la misma fila. En estos retos debes fijarte bien en la referencia, la tabla y el numero de columna.',
            'example' => '=BUSCARV(H2,A2:D10,3,FALSO)',
        ];
    }

    if (str_contains($formula, 'PROMEDIO.SI')) {
        return [
            'title' => 'Explicacion del nivel',
            'explanation' => 'PROMEDIO.SI calcula la media solo de los valores que cumplen una condicion. Primero se define donde se revisa el criterio y luego el rango que se promedia.',
            'example' => '=PROMEDIO.SI(A2:A10,"Marketing",B2:B10)',
        ];
    }

    if (str_contains($formula, 'SUMAR.SI')) {
        return [
            'title' => 'Explicacion del nivel',
            'explanation' => 'SUMAR.SI suma un rango solo cuando otro rango cumple una condicion. Es util para totalizar ventas, zonas, estados o categorias concretas.',
            'example' => '=SUMAR.SI(A2:A10,"Norte",B2:B10)',
        ];
    }

    if (preg_match('/=SI\(/u', $formula) === 1) {
        return [
            'title' => 'Explicacion del nivel',
            'explanation' => 'SI permite tomar decisiones en Excel. Evalua una condicion, devuelve un valor si se cumple y otro distinto si no se cumple.',
            'example' => '=SI(B2>=70,"Aprobado","Reforzar")',
        ];
    }

    if (str_contains($formula, 'PROMEDIO(')) {
        return [
            'title' => 'Explicacion del nivel',
            'explanation' => 'PROMEDIO suma todos los valores del rango y los divide por la cantidad de datos numericos. Se usa para obtener una media rapida de resultados o cantidades.',
            'example' => '=PROMEDIO(B2:B6)',
        ];
    }

    if (str_contains($formula, 'CONTAR(')) {
        return [
            'title' => 'Explicacion del nivel',
            'explanation' => 'CONTAR devuelve cuantas celdas numericas hay dentro de un rango. Sirve para saber cuantos datos validos tienes en una lista.',
            'example' => '=CONTAR(B2:B10)',
        ];
    }

    if (str_contains($formula, 'MAX(')) {
        return [
            'title' => 'Explicacion del nivel',
            'explanation' => 'MAX encuentra el valor mas alto de un rango. Es la forma rapida de detectar el mejor resultado, el mayor precio o el pico de una serie.',
            'example' => '=MAX(C2:C10)',
        ];
    }

    if (str_contains($formula, 'MIN(')) {
        return [
            'title' => 'Explicacion del nivel',
            'explanation' => 'MIN devuelve el valor mas bajo de un rango. Es util para localizar el minimo costo, la menor nota o el dato mas pequeno.',
            'example' => '=MIN(C2:C10)',
        ];
    }

    if (str_contains($formula, 'SUMA(')) {
        return [
            'title' => 'Explicacion del nivel',
            'explanation' => 'SUMA agrega varios valores o un rango completo en una sola formula. Es la base para totalizar listas de cantidades, costos o ventas.',
            'example' => '=SUMA(B2:B6)',
        ];
    }

    if (str_contains($formula, '*') && str_contains($formula, '+')) {
        return [
            'title' => 'Explicacion del nivel',
            'explanation' => 'En este tipo de nivel combinas operaciones. Conviene resolver primero multiplicaciones o divisiones y usar parentesis si necesitas controlar el orden del calculo.',
            'example' => '=B2*C2+D2',
        ];
    }

    if (str_contains($formula, '/')) {
        return [
            'title' => 'Explicacion del nivel',
            'explanation' => 'La division compara una cantidad con otra. Este tipo de formula se usa para razones, promedios simples o indicadores de rendimiento.',
            'example' => '=D4/C4',
        ];
    }

    if (str_contains($formula, '*') || str_contains($category, 'MULTIPLICACION')) {
        return [
            'title' => 'Explicacion del nivel',
            'explanation' => 'La multiplicacion sirve para calcular totales por cantidad, precio por unidad o combinaciones de dos valores relacionados.',
            'example' => '=B3*C3',
        ];
    }

    if (str_contains($formula, '-') || str_contains($category, 'RESTA')) {
        return [
            'title' => 'Explicacion del nivel',
            'explanation' => 'La resta se usa para obtener diferencias entre dos o mas valores. Te ayuda a calcular descuentos, faltantes o variaciones.',
            'example' => '=D3-C3',
        ];
    }

    if (str_contains($formula, '+') || str_contains($category, 'SUMA')) {
        return [
            'title' => 'Explicacion del nivel',
            'explanation' => 'La suma combina valores para obtener un total. En estos ejercicios debes fijarte en las celdas correctas y en el orden de la operacion si hay mas de un paso.',
            'example' => '=B2+C2',
        ];
    }

    return [
        'title' => 'Explicacion del nivel',
        'explanation' => 'Analiza la consigna, identifica la celda objetivo y detecta que tipo de calculo o funcion necesita el nivel. La clave es reconocer la estructura antes de escribir la formula.',
        'example' => '=SUMA(B2:B6)',
    ];
}

function build_level_tables(array $level): array
{
    $number = (int) $level['numero'];
    $formula = normalize_formula((string) $level['respuesta_correcta']);

    if ($number <= 5) {
        return [[
            'title' => 'Inventario Tech',
            'target' => (string) $level['formula_target'],
            'columns' => ['A', 'B', 'C', 'D', 'E'],
            'rows' => [
                ['row' => 1, 'cells' => ['A' => 'Producto', 'B' => 'Precio', 'C' => 'Stock', 'D' => 'Descuento', 'E' => 'Total']],
                ['row' => 2, 'cells' => ['A' => 'Laptop', 'B' => '899', 'C' => '15', 'D' => '50', 'E' => '']],
                ['row' => 3, 'cells' => ['A' => 'Mouse', 'B' => '35', 'C' => '120', 'D' => '5', 'E' => '']],
                ['row' => 4, 'cells' => ['A' => 'Teclado', 'B' => '65', 'C' => '80', 'D' => '12', 'E' => '']],
                ['row' => 5, 'cells' => ['A' => 'Monitor', 'B' => '420', 'C' => '25', 'D' => '35', 'E' => '']],
                ['row' => 6, 'cells' => ['A' => 'Webcam', 'B' => '55', 'C' => '60', 'D' => '8', 'E' => '']],
            ],
        ]];
    }

    if ($number <= 10) {
        return [[
            'title' => 'Notas del Trimestre',
            'target' => (string) $level['formula_target'],
            'columns' => ['A', 'B', 'C', 'D', 'E'],
            'rows' => [
                ['row' => 1, 'cells' => ['A' => 'Alumno', 'B' => 'Examen 1', 'C' => 'Examen 2', 'D' => 'Proyecto', 'E' => 'Final']],
                ['row' => 2, 'cells' => ['A' => 'Ana', 'B' => '85', 'C' => '92', 'D' => '78', 'E' => '']],
                ['row' => 3, 'cells' => ['A' => 'Luis', 'B' => '72', 'C' => '68', 'D' => '88', 'E' => '']],
                ['row' => 4, 'cells' => ['A' => 'María', 'B' => '95', 'C' => '90', 'D' => '94', 'E' => '']],
                ['row' => 5, 'cells' => ['A' => 'Carlos', 'B' => '60', 'C' => '75', 'D' => '70', 'E' => '']],
                ['row' => 6, 'cells' => ['A' => 'Sofía', 'B' => '88', 'C' => '82', 'D' => '91', 'E' => '']],
            ],
        ]];
    }

    if ($number <= 15) {
        return [[
            'title' => 'Gastos Mensuales',
            'target' => (string) $level['formula_target'],
            'columns' => ['A', 'B', 'C', 'D', 'E'],
            'rows' => [
                ['row' => 1, 'cells' => ['A' => 'Mes', 'B' => 'Renta', 'C' => 'Servicios', 'D' => 'Comida', 'E' => 'Balance']],
                ['row' => 2, 'cells' => ['A' => 'Enero', 'B' => '800', 'C' => '150', 'D' => '400', 'E' => '']],
                ['row' => 3, 'cells' => ['A' => 'Febrero', 'B' => '800', 'C' => '130', 'D' => '380', 'E' => '']],
                ['row' => 4, 'cells' => ['A' => 'Marzo', 'B' => '850', 'C' => '145', 'D' => '420', 'E' => '']],
                ['row' => 5, 'cells' => ['A' => 'Abril', 'B' => '850', 'C' => '160', 'D' => '390', 'E' => '']],
                ['row' => 6, 'cells' => ['A' => 'Mayo', 'B' => '900', 'C' => '155', 'D' => '410', 'E' => '']],
            ],
        ]];
    }

    if ($number <= 20) {
        return [[
            'title' => 'Liga Deportiva',
            'target' => (string) $level['formula_target'],
            'columns' => ['A', 'B', 'C', 'D', 'E'],
            'rows' => [
                ['row' => 1, 'cells' => ['A' => 'Jugador', 'B' => 'Goles', 'C' => 'Asist.', 'D' => 'Partidos', 'E' => 'Puntos']],
                ['row' => 2, 'cells' => ['A' => 'Torres', 'B' => '14', 'C' => '8', 'D' => '22', 'E' => '']],
                ['row' => 3, 'cells' => ['A' => 'Méndez', 'B' => '6', 'C' => '18', 'D' => '20', 'E' => '']],
                ['row' => 4, 'cells' => ['A' => 'Rivera', 'B' => '10', 'C' => '5', 'D' => '24', 'E' => '']],
                ['row' => 5, 'cells' => ['A' => 'Vargas', 'B' => '22', 'C' => '3', 'D' => '19', 'E' => '']],
                ['row' => 6, 'cells' => ['A' => 'Salazar', 'B' => '4', 'C' => '12', 'D' => '17', 'E' => '']],
            ],
        ]];
    }

    if ($number <= 30) {
        return [[
            'title' => 'Encuesta de Satisfacción',
            'target' => (string) $level['formula_target'],
            'columns' => ['A', 'B', 'C', 'D', 'E', 'F'],
            'rows' => [
                ['row' => 1, 'cells' => ['A' => 'Agente', 'B' => 'Sem 1', 'C' => 'Sem 2', 'D' => 'Sem 3', 'E' => 'Sem 4', 'F' => 'Score']],
                ['row' => 2, 'cells' => ['A' => 'Valeria', 'B' => '82', 'C' => '88', 'D' => '91', 'E' => '85', 'F' => '']],
                ['row' => 3, 'cells' => ['A' => 'Andrés', 'B' => '75', 'C' => '70', 'D' => '78', 'E' => '80', 'F' => '']],
                ['row' => 4, 'cells' => ['A' => 'Camila', 'B' => '90', 'C' => '94', 'D' => '87', 'E' => '92', 'F' => '']],
                ['row' => 5, 'cells' => ['A' => 'Diego', 'B' => '68', 'C' => '72', 'D' => '65', 'E' => '74', 'F' => '']],
                ['row' => 6, 'cells' => ['A' => 'Elena', 'B' => '95', 'C' => '89', 'D' => '93', 'E' => '96', 'F' => '']],
                ['row' => 7, 'cells' => ['A' => 'Fabián', 'B' => '60', 'C' => '66', 'D' => '71', 'E' => '69', 'F' => '']],
                ['row' => 8, 'cells' => ['A' => 'Gina', 'B' => '84', 'C' => '80', 'D' => '86', 'E' => '88', 'F' => '']],
                ['row' => 9, 'cells' => ['A' => 'Héctor', 'B' => '77', 'C' => '83', 'D' => '79', 'E' => '81', 'F' => '']],
                ['row' => 10, 'cells' => ['A' => 'Isabel', 'B' => '91', 'C' => '87', 'D' => '90', 'E' => '93', 'F' => '']],
            ],
        ]];
    }

    if ($number <= 40) {
        return [[
            'title' => 'Control de Producción',
            'target' => (string) $level['formula_target'],
            'columns' => ['A', 'B', 'C', 'D', 'E', 'F'],
            'rows' => [
                ['row' => 1, 'cells' => ['A' => 'Línea', 'B' => 'Lunes', 'C' => 'Martes', 'D' => 'Miércoles', 'E' => 'Jueves', 'F' => 'Reporte']],
                ['row' => 2, 'cells' => ['A' => 'Línea A', 'B' => '340', 'C' => '360', 'D' => '355', 'E' => '380', 'F' => '']],
                ['row' => 3, 'cells' => ['A' => 'Línea B', 'B' => '290', 'C' => '310', 'D' => '300', 'E' => '295', 'F' => '']],
                ['row' => 4, 'cells' => ['A' => 'Línea C', 'B' => '410', 'C' => '420', 'D' => '400', 'E' => '430', 'F' => '']],
                ['row' => 5, 'cells' => ['A' => 'Línea D', 'B' => '250', 'C' => '265', 'D' => '270', 'E' => '285', 'F' => '']],
                ['row' => 6, 'cells' => ['A' => 'Línea E', 'B' => '380', 'C' => '375', 'D' => '390', 'E' => '395', 'F' => '']],
                ['row' => 7, 'cells' => ['A' => 'Línea F', 'B' => '315', 'C' => '330', 'D' => '340', 'E' => '325', 'F' => '']],
                ['row' => 8, 'cells' => ['A' => 'Línea G', 'B' => '425', 'C' => '415', 'D' => '435', 'E' => '440', 'F' => '']],
                ['row' => 9, 'cells' => ['A' => 'Línea H', 'B' => '265', 'C' => '280', 'D' => '275', 'E' => '290', 'F' => '']],
                ['row' => 10, 'cells' => ['A' => 'Línea I', 'B' => '350', 'C' => '345', 'D' => '365', 'E' => '370', 'F' => '']],
            ],
        ]];
    }

    if (str_starts_with($formula, '=si(')) {
        return [[
            'title' => 'Reglas de decisión',
            'target' => (string) $level['formula_target'],
            'columns' => ['A', 'B', 'C', 'D', 'E'],
            'rows' => [
                ['row' => 1, 'cells' => ['A' => 'Caso', 'B' => 'Nota', 'C' => 'Stock', 'D' => 'Ventas', 'E' => 'Estado']],
                ['row' => 2, 'cells' => ['A' => 'Alumno A', 'B' => '74', 'C' => '8', 'D' => '1200', 'E' => 'Pendiente'] ],
                ['row' => 3, 'cells' => ['A' => 'Alumno B', 'B' => '100', 'C' => '15', 'D' => '850', 'E' => 'Pendiente'] ],
                ['row' => 4, 'cells' => ['A' => 'Proyecto C', 'B' => '66', 'C' => '12', 'D' => '92', 'E' => 'Pendiente'] ],
                ['row' => 5, 'cells' => ['A' => 'Servicio D', 'B' => '80', 'C' => '4', 'D' => '64', 'E' => 'Pendiente'] ],
                ['row' => 6, 'cells' => ['A' => 'Factura E', 'B' => '54', 'C' => '5', 'D' => '48', 'E' => 'Pago'] ],
            ],
        ]];
    }

    if (str_contains($formula, 'sumar.si(a2:a8,"norte"') || str_contains($formula, 'sumar.si(a2:a10,"oeste"')) {
        return [[
            'title' => 'Ventas por región',
            'target' => (string) $level['formula_target'],
            'columns' => ['A', 'B', 'C', 'D'],
            'rows' => [
                ['row' => 1, 'cells' => ['A' => 'Región', 'B' => 'Ventas', 'C' => 'Costos', 'D' => 'Utilidad']],
                ['row' => 2, 'cells' => ['A' => 'Norte', 'B' => '220', 'C' => '140', 'D' => '80'] ],
                ['row' => 3, 'cells' => ['A' => 'Sur', 'B' => '180', 'C' => '120', 'D' => '60'] ],
                ['row' => 4, 'cells' => ['A' => 'Norte', 'B' => '260', 'C' => '160', 'D' => '100'] ],
                ['row' => 5, 'cells' => ['A' => 'Este', 'B' => '150', 'C' => '95', 'D' => '55'] ],
                ['row' => 6, 'cells' => ['A' => 'Oeste', 'B' => '240', 'C' => '170', 'D' => '70'] ],
                ['row' => 7, 'cells' => ['A' => 'Oeste', 'B' => '210', 'C' => '150', 'D' => '60'] ],
                ['row' => 8, 'cells' => ['A' => 'Norte', 'B' => '195', 'C' => '122', 'D' => '73'] ],
                ['row' => 9, 'cells' => ['A' => 'Oeste', 'B' => '280', 'C' => '188', 'D' => '92'] ],
                ['row' => 10, 'cells' => ['A' => 'Sur', 'B' => '175', 'C' => '110', 'D' => '65'] ],
            ],
        ]];
    }

    if (
        str_contains($formula, 'sumar.si(c2:c9,">=50"')
        || str_contains($formula, 'promedio.si(c2:c8,">=70"')
        || str_contains($formula, 'promedio.si(c2:c8,">=80"')
        || str_contains($formula, 'promedio.si(c2:c9,">=80"')
        || str_contains($formula, 'promedio.si(d2:d9,">0"')
    ) {
        return [[
            'title' => 'Resultados por puntaje',
            'target' => (string) $level['formula_target'],
            'columns' => ['A', 'B', 'C', 'D'],
            'rows' => [
                ['row' => 1, 'cells' => ['A' => 'Alumno', 'B' => 'Grupo', 'C' => 'Puntaje', 'D' => 'Resultado']],
                ['row' => 2, 'cells' => ['A' => 'Lucía', 'B' => 'A', 'C' => '92', 'D' => '88'] ],
                ['row' => 3, 'cells' => ['A' => 'Pablo', 'B' => 'B', 'C' => '48', 'D' => '52'] ],
                ['row' => 4, 'cells' => ['A' => 'Elena', 'B' => 'A', 'C' => '77', 'D' => '81'] ],
                ['row' => 5, 'cells' => ['A' => 'Mario', 'B' => 'B', 'C' => '64', 'D' => '67'] ],
                ['row' => 6, 'cells' => ['A' => 'Inés', 'B' => 'A', 'C' => '85', 'D' => '90'] ],
                ['row' => 7, 'cells' => ['A' => 'Joel', 'B' => 'B', 'C' => '55', 'D' => '60'] ],
                ['row' => 8, 'cells' => ['A' => 'Nora', 'B' => 'A', 'C' => '73', 'D' => '78'] ],
                ['row' => 9, 'cells' => ['A' => 'Teo', 'B' => 'B', 'C' => '96', 'D' => '94'] ],
            ],
        ]];
    }

    if (str_contains($formula, 'promedio.si(a2:a7,"marketing"') || str_contains($formula, 'promedio.si(a2:a8,"ventas"')) {
        return [[
            'title' => 'Rendimiento por área',
            'target' => (string) $level['formula_target'],
            'columns' => ['A', 'B', 'C'],
            'rows' => [
                ['row' => 1, 'cells' => ['A' => 'Área', 'B' => 'Resultado', 'C' => 'Bono']],
                ['row' => 2, 'cells' => ['A' => 'Marketing', 'B' => '68', 'C' => '120'] ],
                ['row' => 3, 'cells' => ['A' => 'Ventas', 'B' => '540', 'C' => '230'] ],
                ['row' => 4, 'cells' => ['A' => 'Marketing', 'B' => '74', 'C' => '125'] ],
                ['row' => 5, 'cells' => ['A' => 'Soporte', 'B' => '62', 'C' => '118'] ],
                ['row' => 6, 'cells' => ['A' => 'Ventas', 'B' => '590', 'C' => '250'] ],
                ['row' => 7, 'cells' => ['A' => 'Marketing', 'B' => '81', 'C' => '130'] ],
                ['row' => 8, 'cells' => ['A' => 'Ventas', 'B' => '505', 'C' => '245'] ],
            ],
        ]];
    }

    if (str_contains($formula, 'sumar.si(b2:b10,"laptop"')) {
        return [[
            'title' => 'Inventario por producto',
            'target' => (string) $level['formula_target'],
            'columns' => ['A', 'B', 'C'],
            'rows' => [
                ['row' => 1, 'cells' => ['A' => 'Código', 'B' => 'Producto', 'C' => 'Unidades']],
                ['row' => 2, 'cells' => ['A' => 'P-01', 'B' => 'Laptop', 'C' => '18'] ],
                ['row' => 3, 'cells' => ['A' => 'P-02', 'B' => 'Mouse', 'C' => '32'] ],
                ['row' => 4, 'cells' => ['A' => 'P-03', 'B' => 'Laptop', 'C' => '21'] ],
                ['row' => 5, 'cells' => ['A' => 'P-04', 'B' => 'Teclado', 'C' => '15'] ],
                ['row' => 6, 'cells' => ['A' => 'P-05', 'B' => 'Laptop', 'C' => '11'] ],
                ['row' => 7, 'cells' => ['A' => 'P-06', 'B' => 'Monitor', 'C' => '10'] ],
                ['row' => 8, 'cells' => ['A' => 'P-07', 'B' => 'Laptop', 'C' => '27'] ],
                ['row' => 9, 'cells' => ['A' => 'P-08', 'B' => 'Mouse', 'C' => '19'] ],
                ['row' => 10, 'cells' => ['A' => 'P-09', 'B' => 'Laptop', 'C' => '16'] ],
            ],
        ]];
    }

    if (str_contains($formula, 'promedio.si(b2:b9,"turnoa"')) {
        return [[
            'title' => 'Asistencia por turno',
            'target' => (string) $level['formula_target'],
            'columns' => ['A', 'B', 'C'],
            'rows' => [
                ['row' => 1, 'cells' => ['A' => 'Persona', 'B' => 'Turno', 'C' => 'Asistencia']],
                ['row' => 2, 'cells' => ['A' => 'Ana', 'B' => 'Turno A', 'C' => '88'] ],
                ['row' => 3, 'cells' => ['A' => 'Luis', 'B' => 'Turno B', 'C' => '74'] ],
                ['row' => 4, 'cells' => ['A' => 'Mía', 'B' => 'Turno A', 'C' => '91'] ],
                ['row' => 5, 'cells' => ['A' => 'Leo', 'B' => 'Turno B', 'C' => '67'] ],
                ['row' => 6, 'cells' => ['A' => 'Sofi', 'B' => 'Turno A', 'C' => '95'] ],
                ['row' => 7, 'cells' => ['A' => 'Paz', 'B' => 'Turno B', 'C' => '70'] ],
                ['row' => 8, 'cells' => ['A' => 'Gael', 'B' => 'Turno A', 'C' => '84'] ],
                ['row' => 9, 'cells' => ['A' => 'Luz', 'B' => 'Turno B', 'C' => '76'] ],
            ],
        ]];
    }

    if (str_contains($formula, 'sumar.si(a2:a8,"si"')) {
        return [[
            'title' => 'Checklist operativo',
            'target' => (string) $level['formula_target'],
            'columns' => ['A', 'B'],
            'rows' => [
                ['row' => 1, 'cells' => ['A' => 'Cumple', 'B' => 'Puntaje']],
                ['row' => 2, 'cells' => ['A' => 'Si', 'B' => '25'] ],
                ['row' => 3, 'cells' => ['A' => 'No', 'B' => '10'] ],
                ['row' => 4, 'cells' => ['A' => 'Si', 'B' => '18'] ],
                ['row' => 5, 'cells' => ['A' => 'No', 'B' => '7'] ],
                ['row' => 6, 'cells' => ['A' => 'Si', 'B' => '22'] ],
                ['row' => 7, 'cells' => ['A' => 'Si', 'B' => '30'] ],
                ['row' => 8, 'cells' => ['A' => 'No', 'B' => '9'] ],
            ],
        ]];
    }

    if (str_contains($formula, 'promedio.si(a2:a9,"activo"') || str_contains($formula, 'promedio.si(a2:a8,"activo"')) {
        return [[
            'title' => 'Clientes activos',
            'target' => (string) $level['formula_target'],
            'columns' => ['A', 'B'],
            'rows' => [
                ['row' => 1, 'cells' => ['A' => 'Estado', 'B' => 'Ingreso']],
                ['row' => 2, 'cells' => ['A' => 'Activo', 'B' => '420'] ],
                ['row' => 3, 'cells' => ['A' => 'Inactivo', 'B' => '180'] ],
                ['row' => 4, 'cells' => ['A' => 'Activo', 'B' => '510'] ],
                ['row' => 5, 'cells' => ['A' => 'Activo', 'B' => '390'] ],
                ['row' => 6, 'cells' => ['A' => 'Inactivo', 'B' => '205'] ],
                ['row' => 7, 'cells' => ['A' => 'Activo', 'B' => '460'] ],
                ['row' => 8, 'cells' => ['A' => 'Inactivo', 'B' => '160'] ],
                ['row' => 9, 'cells' => ['A' => 'Activo', 'B' => '530'] ],
            ],
        ]];
    }

    if (str_contains($formula, 'sumar.si(c2:c8,"rojo"')) {
        return [[
            'title' => 'Pedidos por color',
            'target' => (string) $level['formula_target'],
            'columns' => ['A', 'B', 'C', 'D'],
            'rows' => [
                ['row' => 1, 'cells' => ['A' => 'Pedido', 'B' => 'Talla', 'C' => 'Color', 'D' => 'Cantidad']],
                ['row' => 2, 'cells' => ['A' => 'A-11', 'B' => 'M', 'C' => 'Rojo', 'D' => '12'] ],
                ['row' => 3, 'cells' => ['A' => 'A-12', 'B' => 'S', 'C' => 'Azul', 'D' => '7'] ],
                ['row' => 4, 'cells' => ['A' => 'A-13', 'B' => 'L', 'C' => 'Rojo', 'D' => '9'] ],
                ['row' => 5, 'cells' => ['A' => 'A-14', 'B' => 'M', 'C' => 'Verde', 'D' => '5'] ],
                ['row' => 6, 'cells' => ['A' => 'A-15', 'B' => 'S', 'C' => 'Rojo', 'D' => '14'] ],
                ['row' => 7, 'cells' => ['A' => 'A-16', 'B' => 'XL', 'C' => 'Azul', 'D' => '8'] ],
                ['row' => 8, 'cells' => ['A' => 'A-17', 'B' => 'M', 'C' => 'Rojo', 'D' => '10'] ],
            ],
        ]];
    }

    if ($number <= 80 || str_contains($formula, 'buscarv(') || str_contains($formula, 'buscarx(') || str_contains($formula, 'si.error(')) {
        return [
            [
                'title' => 'Tabla de búsqueda',
                'target' => (string) $level['formula_target'],
                'columns' => ['A', 'B', 'C', 'D', 'E', 'F'],
                'rows' => [
                    ['row' => 1, 'cells' => ['A' => 'Código', 'B' => 'Producto', 'C' => 'Categoría', 'D' => 'Precio', 'E' => 'Stock', 'F' => 'Responsable']],
                    ['row' => 2, 'cells' => ['A' => 'P100', 'B' => 'Laptop', 'C' => 'Tecnología', 'D' => '950', 'E' => '14', 'F' => 'Alicia'] ],
                    ['row' => 3, 'cells' => ['A' => 'P200', 'B' => 'Tablet', 'C' => 'Tecnología', 'D' => '620', 'E' => '18', 'F' => 'Bruno'] ],
                    ['row' => 4, 'cells' => ['A' => 'P300', 'B' => 'Mouse', 'C' => 'Accesorios', 'D' => '45', 'E' => '65', 'F' => 'Carla'] ],
                    ['row' => 5, 'cells' => ['A' => 'P400', 'B' => 'Monitor', 'C' => 'Tecnología', 'D' => '310', 'E' => '11', 'F' => 'Diego'] ],
                    ['row' => 6, 'cells' => ['A' => 'P500', 'B' => 'Teclado', 'C' => 'Accesorios', 'D' => '70', 'E' => '29', 'F' => 'Elena'] ],
                    ['row' => 7, 'cells' => ['A' => 'P600', 'B' => 'Impresora', 'C' => 'Oficina', 'D' => '410', 'E' => '8', 'F' => 'Fabio'] ],
                    ['row' => 8, 'cells' => ['A' => 'P700', 'B' => 'Cámara', 'C' => 'Multimedia', 'D' => '520', 'E' => '13', 'F' => 'Gina'] ],
                    ['row' => 9, 'cells' => ['A' => 'P800', 'B' => 'Router', 'C' => 'Redes', 'D' => '130', 'E' => '22', 'F' => 'Hugo'] ],
                    ['row' => 10, 'cells' => ['A' => 'P900', 'B' => 'Auriculares', 'C' => 'Audio', 'D' => '95', 'E' => '37', 'F' => 'Iris'] ],
                ],
            ],
            [
                'title' => 'Celdas de consulta',
                'target' => (string) $level['formula_target'],
                'columns' => ['G', 'H', 'I'],
                'rows' => [
                    ['row' => 1, 'cells' => ['G' => 'Buscar', 'H' => 'Código', 'I' => 'Código alterno']],
                    ['row' => 2, 'cells' => ['G' => 'Consulta 1', 'H' => 'P400', 'I' => 'P800'] ],
                    ['row' => 3, 'cells' => ['G' => 'Consulta 2', 'H' => 'P600', 'I' => 'P200'] ],
                    ['row' => 4, 'cells' => ['G' => 'Consulta 3', 'H' => 'P300', 'I' => 'P100'] ],
                    ['row' => 5, 'cells' => ['G' => 'Consulta 4', 'H' => 'P999', 'I' => 'P700'] ],
                ],
            ],
        ];
    }

    if (str_contains($formula, 'curso1') || str_contains($formula, 'curso2')) {
        return [[
            'title' => 'Inscripciones por curso',
            'target' => (string) $level['formula_target'],
            'columns' => ['A', 'B', 'C'],
            'rows' => [
                ['row' => 1, 'cells' => ['A' => 'Alumno', 'B' => 'Curso', 'C' => 'Horas']],
                ['row' => 2, 'cells' => ['A' => 'Ana', 'B' => 'Curso 1', 'C' => '4'] ],
                ['row' => 3, 'cells' => ['A' => 'Luis', 'B' => 'Curso 2', 'C' => '6'] ],
                ['row' => 4, 'cells' => ['A' => 'Mía', 'B' => 'Curso 3', 'C' => '2'] ],
                ['row' => 5, 'cells' => ['A' => 'Leo', 'B' => 'Curso 1', 'C' => '5'] ],
                ['row' => 6, 'cells' => ['A' => 'Paz', 'B' => 'Curso 2', 'C' => '7'] ],
                ['row' => 7, 'cells' => ['A' => 'Nora', 'B' => 'Curso 1', 'C' => '3'] ],
                ['row' => 8, 'cells' => ['A' => 'Joel', 'B' => 'Curso 2', 'C' => '4'] ],
                ['row' => 9, 'cells' => ['A' => 'Luz', 'B' => 'Curso 4', 'C' => '2'] ],
            ],
        ]];
    }

    return [[
        'title' => 'Caso integral',
        'target' => (string) $level['formula_target'],
        'columns' => ['A', 'B', 'C', 'D', 'E', 'F'],
        'rows' => [
            ['row' => 1, 'cells' => ['A' => 'Equipo', 'B' => 'Ingresos', 'C' => 'Costos', 'D' => 'Calidad', 'E' => 'Estado', 'F' => 'Responsable']],
            ['row' => 2, 'cells' => ['A' => 'Ventas', 'B' => '620', 'C' => '410', 'D' => '88', 'E' => 'Activo', 'F' => 'Alicia'] ],
            ['row' => 3, 'cells' => ['A' => 'Soporte', 'B' => '340', 'C' => '280', 'D' => '72', 'E' => 'Activo', 'F' => 'Bruno'] ],
            ['row' => 4, 'cells' => ['A' => 'Ventas', 'B' => '580', 'C' => '360', 'D' => '91', 'E' => 'Activo', 'F' => 'Carla'] ],
            ['row' => 5, 'cells' => ['A' => 'Operaciones', 'B' => '410', 'C' => '390', 'D' => '65', 'E' => 'Inactivo', 'F' => 'Diego'] ],
            ['row' => 6, 'cells' => ['A' => 'Marketing', 'B' => '470', 'C' => '250', 'D' => '82', 'E' => 'Activo', 'F' => 'Elena'] ],
            ['row' => 7, 'cells' => ['A' => 'Ventas', 'B' => '530', 'C' => '320', 'D' => '86', 'E' => 'Activo', 'F' => 'Fabio'] ],
            ['row' => 8, 'cells' => ['A' => 'Oeste', 'B' => '280', 'C' => '188', 'D' => '92', 'E' => 'Activo', 'F' => 'Gina'] ],
            ['row' => 9, 'cells' => ['A' => 'Oeste', 'B' => '260', 'C' => '170', 'D' => '84', 'E' => 'Activo', 'F' => 'Hugo'] ],
            ['row' => 10, 'cells' => ['A' => 'Norte', 'B' => '300', 'C' => '200', 'D' => '89', 'E' => 'Activo', 'F' => 'Iris'] ],
        ],
    ]];
}

function generate_distractors(array $level, int $count = 3): array
{
    $correct = (string) $level['respuesta_correcta'];
    $pool = [];
    $normCorrect = normalize_formula($correct);

    $fnSwaps = [
        'SUMA' => 'PROMEDIO', 'PROMEDIO' => 'SUMA',
        'MAX' => 'MIN', 'MIN' => 'MAX',
        'BUSCARV' => 'BUSCARX', 'BUSCARX' => 'BUSCARV',
        'CONTAR' => 'SUMA',
        'SUMAR.SI' => 'CONTAR.SI', 'PROMEDIO.SI' => 'SUMAR.SI',
    ];
    foreach ($fnSwaps as $from => $to) {
        if (mb_stripos($correct, $from) !== false) {
            $pool[] = str_ireplace($from, $to, $correct);
            break;
        }
    }

    $pool[] = preg_replace_callback('/([A-E])(\d+)/', static function (array $m): string {
        return $m[1] . ((int) $m[2] + 1);
    }, $correct) ?? $correct;

    $pool[] = preg_replace_callback('/([A-E])(\d+)/', static function (array $m): string {
        $col = chr(min(ord('F'), ord($m[1]) + 1));
        return $col . $m[2];
    }, $correct) ?? $correct;

    $pool[] = preg_replace_callback('/(:)([A-Z])(\d+)/', static function (array $m): string {
        return $m[1] . $m[2] . max(2, (int) $m[3] - 1);
    }, $correct) ?? $correct;

    if (!empty($level['respuestas_alternativas'])) {
        foreach (explode('||', (string) $level['respuestas_alternativas']) as $alt) {
            $alt = trim($alt);
            if ($alt !== '' && normalize_formula($alt) !== $normCorrect) {
                $pool[] = $alt;
            }
        }
    }

    $distractors = [];
    foreach ($pool as $candidate) {
        if (normalize_formula($candidate) !== $normCorrect && !in_array($candidate, $distractors, true)) {
            $distractors[] = $candidate;
        }
        if (count($distractors) >= $count) {
            break;
        }
    }

    $fallbacks = ['=ERROR()', '=FALSO()', '=NULO()'];
    $fi = 0;
    while (count($distractors) < $count) {
        $distractors[] = $fallbacks[$fi % count($fallbacks)];
        $fi++;
    }

    return array_slice($distractors, 0, $count);
}

function render_excel_tables(array $tables, string $targetCell): string
{
    ob_start();
    foreach ($tables as $table) {
        $headerLabels = [];
        if (!empty($table['rows'])) {
            $firstRow = $table['rows'][0];
            foreach ($table['columns'] as $col) {
                $headerLabels[$col] = $firstRow['cells'][$col] ?? $col;
            }
        }

        echo '<section class="excel-card">';
        echo '<div class="excel-card__header">';
        echo '<h3>' . e($table['title']) . '</h3>';
        echo '<span class="excel-card__target">Celda objetivo: ' . e($targetCell) . '</span>';
        echo '</div>';
        echo '<div class="excel-grid-wrapper">';
        echo '<table class="excel-grid">';
        echo '<thead><tr><th>#</th>';
        foreach ($table['columns'] as $column) {
            echo '<th>' . e($column) . '</th>';
        }
        echo '</tr></thead><tbody>';
        foreach ($table['rows'] as $rowIndex => $row) {
            $isHeaderRow = ($rowIndex === 0);
            echo '<tr' . ($isHeaderRow ? ' class="excel-grid__header-row"' : '') . '>';
            echo '<th>' . e((string) $row['row']) . '</th>';
            foreach ($table['columns'] as $column) {
                $cellId = $column . $row['row'];
                $isTarget = $cellId === $targetCell;
                $label = $column . ' · ' . ($headerLabels[$column] ?? $column);
                $cls = $isTarget ? ' class="is-target"' : '';
                echo '<td data-label="' . e($label) . '"' . $cls . '>' . e((string) ($row['cells'][$column] ?? '')) . '</td>';
            }
            echo '</tr>';
        }
        echo '</tbody></table>';
        echo '</div>';
        echo '</section>';
    }

    return (string) ob_get_clean();
}
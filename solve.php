<?php
function solve() {
    // Открываем стандартный ввод для чтения
    $stdin = fopen('php://stdin', 'r');

    // Читаем первую непустую строку (ожидаем n и m)
    do {
        $line = fgets($stdin);
        if ($line === false) {
            // Если вход пустой — выводим NO и завершаем
            echo "NO\n";
            return;
        }
        $line = trim($line);
    } while ($line === '');

    // Разбиваем строку по пробельным символам
    $parts = preg_split('/\s+/', $line);
    if (count($parts) < 2) {
        // Некорректный формат первой строки
        echo "NO\n";
        return;
    }
    $n = (int)$parts[0]; // количество вершин (предметов)
    $m = (int)$parts[1]; // количество правил обмена (рёбер)

    // Большое значение, используемое как "бесконечность"
    $INF = PHP_INT_MAX / 4;

    // Инициализация матриц dist и next (1‑индексация)
    $dist = [];
    $next = [];
    for ($i = 1; $i <= $n; $i++) {
        // Заполняем строку dist значениями INF
        $dist[$i] = array_fill(1, $n, $INF);
        // Заполняем строку next значениями -1 (нет пути)
        $next[$i] = array_fill(1, $n, -1);
        // Расстояние до самой себя равно 0
        $dist[$i][$i] = 0;
        // next на диагонали указываем на саму вершину
        $next[$i][$i] = $i;
    }

    // Чтение m рёбер из входа
    $read = 0;
    while ($read < $m && ($line = fgets($stdin)) !== false) {
        $line = trim($line);
        if ($line === '') continue; // пропускаем пустые строки
        $parts = preg_split('/\s+/', $line);
        if (count($parts) < 3) continue; // некорректная строка — пропускаем
        $u = (int)$parts[0]; // из u
        $v = (int)$parts[1]; // в v
        $w = (int)$parts[2]; // вес (золото, отрицательное — игрок получает золото)
        // Если между u и v несколько рёбер, сохраняем минимальный вес
        if ($w < $dist[$u][$v]) {
            $dist[$u][$v] = $w;
            $next[$u][$v] = $v;
        }
        $read++;
    }
    fclose($stdin);

    // Алгоритм Флойда–Уоршелла для поиска кратчайших путей
    for ($k = 1; $k <= $n; $k++) {
        for ($i = 1; $i <= $n; $i++) {
            // Если i->k недостижим, пропускаем
            if ($dist[$i][$k] === $INF) continue;
            for ($j = 1; $j <= $n; $j++) {
                // Если k->j недостижим, пропускаем
                if ($dist[$k][$j] === $INF) continue;
                $new = $dist[$i][$k] + $dist[$k][$j];
                // Если найден более короткий путь i->j через k
                if ($new < $dist[$i][$j]) {
                    $dist[$i][$j] = $new;
                    // Обновляем next для восстановления пути
                    $next[$i][$j] = $next[$i][$k];
                }
            }
        }
    }

    // Ищем вершину, для которой dist[i][i] < 0 — это признак отрицательного цикла
    $start = -1;
    for ($i = 1; $i <= $n; $i++) {
        if ($dist[$i][$i] < 0) {
            $start = $i;
            break;
        }
    }

    // Если отрицательных циклов нет — выводим NO
    if ($start == -1) {
        echo "NO\n";
        return;
    }

    // Надёжное восстановление цикла:
    // 1) Сначала делаем до n шагов по next, чтобы гарантированно попасть внутрь цикла
    $v = $start;
    for ($t = 0; $t < $n; $t++) {
        if ($next[$v][$start] === -1) break; // если путь не определён — выходим
        $v = $next[$v][$start];
    }

    // 2) Собираем вершины цикла, начиная с v, пока не вернёмся в уже посещённую вершину
    $cycle = [];
    $cur = $v;
    $seen = [];
    while (true) {
        if ($cur === -1) break; // защита от ошибок
        if (isset($seen[$cur])) break; // встретили повтор — цикл закрыт
        $seen[$cur] = true;
        $cycle[] = $cur;
        $cur = $next[$cur][$start];
        // дополнительная защита: если цикл слишком длинный — прерываем
        if (count($cycle) > $n + 5) break;
    }

    // Если цикл не собрался, пробуем запасной вариант: следуем от start ровно n шагов
    if (count($cycle) == 0) {
        $cur = $start;
        $cycle = [];
        for ($i = 0; $i < $n; $i++) {
            if ($cur == -1) break;
            $cycle[] = $cur;
            $cur = $next[$cur][$start];
        }
    }

    // Если всё ещё нет цикла — сообщаем NO
    if (count($cycle) == 0) {
        echo "NO\n";
        return;
    }

    // Поворачиваем цикл так, чтобы он начинался с минимальной вершины (по условию)
    $minVal = min($cycle);
    $minIdx = array_search($minVal, $cycle);
    $rot = array_merge(array_slice($cycle, $minIdx), array_slice($cycle, 0, $minIdx));
    // Закрываем цикл, повторив первую вершину в конце
    $rot[] = $rot[0];

    // Выводим результат
    echo "YES\n";
    echo implode(' ', $rot) . "\n";
}

solve();

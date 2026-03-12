<?php
// Fix contabilidad.php - replace corrupted script block
$file = dirname(__DIR__) . '/views/contabilidad.php';
$content = file_get_contents($file);

// Find the <script> position (last occurrence of the corrupted script)
$scriptStart = strpos($content, '<script>');

if ($scriptStart === false) {
    die('No script tag found');
}

// Keep everything before the first <script> tag and close the main divs properly
// First find where the corrupted script begins (after </div><!-- /main-content -->)
$cutMarker = "</div><!-- /main-content -->";
$cutPos = strpos($content, $cutMarker);

if ($cutPos === false) {
    die('Cut marker not found');
}

$cutPos += strlen($cutMarker);

// Build the replacement tail
$weekDays = json_encode(['Lun','Mar','Mié','Jue','Vie','Sáb','Dom']);

$tail = <<<'HTML'

<script>
// ── Chart.js Weekly ─────────────────────────────────────────────────────────
const weekLabels = WEEK_DAYS_JSON;
const weekFact   = WEEK_FACT_JSON;
const weekRec    = WEEK_REC_JSON;

const ctx = document.getElementById('weekChart').getContext('2d');

const gradFact = ctx.createLinearGradient(0, 0, 0, 240);
gradFact.addColorStop(0, 'rgba(99,102,241,.5)');
gradFact.addColorStop(1, 'rgba(99,102,241,.02)');

const gradRec = ctx.createLinearGradient(0, 0, 0, 240);
gradRec.addColorStop(0, 'rgba(16,185,129,.5)');
gradRec.addColorStop(1, 'rgba(16,185,129,.02)');

new Chart(ctx, {
    type: 'bar',
    data: {
        labels: weekLabels,
        datasets: [
            {
                label: 'Facturado',
                data: weekFact,
                backgroundColor: 'rgba(99,102,241,.7)',
                borderColor: '#6366f1',
                borderWidth: 1.5,
                borderRadius: 6,
                borderSkipped: false,
            },
            {
                label: 'Recaudado',
                data: weekRec,
                type: 'line',
                fill: true,
                backgroundColor: gradRec,
                borderColor: '#10b981',
                borderWidth: 2,
                pointBackgroundColor: '#10b981',
                pointRadius: 4,
                tension: 0.4,
            }
        ]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        interaction: { mode: 'index', intersect: false },
        plugins: {
            legend: { display: false },
            tooltip: {
                backgroundColor: 'rgba(15,23,42,.95)',
                borderColor: 'rgba(255,255,255,.08)',
                borderWidth: 1,
                titleColor: '#f1f5f9',
                bodyColor: '#94a3b8',
                padding: 12,
                callbacks: {
                    label: ctx2 => ' ' + ctx2.dataset.label + ': $' +
                        Number(ctx2.parsed.y).toLocaleString('es-CO')
                }
            }
        },
        scales: {
            x: {
                grid: { color: 'rgba(255,255,255,.04)', drawBorder: false },
                ticks: { color: '#94a3b8', font: { size: 12 } }
            },
            y: {
                grid: { color: 'rgba(255,255,255,.04)', drawBorder: false },
                ticks: {
                    color: '#94a3b8',
                    font: { size: 11 },
                    callback: v => '$' + (v >= 1000000 ? (v/1000000).toFixed(1)+'M' : v >= 1000 ? (v/1000).toFixed(0)+'K' : v)
                }
            }
        }
    }
});
</script>
</body>
</html>
HTML;

// Now we need to inject PHP echo for the week data
// We need to extract the week data from PHP - use a PHP include trick instead
// Actually build the tail with PHP tags intact:
$phpTail = "\n<script>\n" .
"// ── Chart.js Weekly ─────────────────────────────────────────────────────────\n" .
"const weekLabels = <?= json_encode(\$weekDays) ?>;\n" .
"const weekFact   = <?= json_encode(array_column(\$weekData,'facturado')) ?>;\n" .
"const weekRec    = <?= json_encode(array_column(\$weekData,'recaudado')) ?>;\n" .
"\n" .
"const ctx = document.getElementById('weekChart').getContext('2d');\n" .
"\n" .
"const gradFact = ctx.createLinearGradient(0,0,0,240);\n" .
"gradFact.addColorStop(0,'rgba(99,102,241,.5)');\n" .
"gradFact.addColorStop(1,'rgba(99,102,241,.02)');\n" .
"\n" .
"const gradRec = ctx.createLinearGradient(0,0,0,240);\n" .
"gradRec.addColorStop(0,'rgba(16,185,129,.5)');\n" .
"gradRec.addColorStop(1,'rgba(16,185,129,.02)');\n" .
"\n" .
"new Chart(ctx, {\n" .
"    type: 'bar',\n" .
"    data: {\n" .
"        labels: weekLabels,\n" .
"        datasets: [\n" .
"            {\n" .
"                label: 'Facturado',\n" .
"                data: weekFact,\n" .
"                backgroundColor: 'rgba(99,102,241,.7)',\n" .
"                borderColor: '#6366f1',\n" .
"                borderWidth: 1.5,\n" .
"                borderRadius: 6,\n" .
"                borderSkipped: false,\n" .
"            },\n" .
"            {\n" .
"                label: 'Recaudado',\n" .
"                data: weekRec,\n" .
"                type: 'line',\n" .
"                fill: true,\n" .
"                backgroundColor: gradRec,\n" .
"                borderColor: '#10b981',\n" .
"                borderWidth: 2,\n" .
"                pointBackgroundColor: '#10b981',\n" .
"                pointRadius: 4,\n" .
"                tension: 0.4,\n" .
"            }\n" .
"        ]\n" .
"    },\n" .
"    options: {\n" .
"        responsive: true,\n" .
"        maintainAspectRatio: false,\n" .
"        interaction: { mode: 'index', intersect: false },\n" .
"        plugins: {\n" .
"            legend: { display: false },\n" .
"            tooltip: {\n" .
"                backgroundColor: 'rgba(15,23,42,.95)',\n" .
"                borderColor: 'rgba(255,255,255,.08)',\n" .
"                borderWidth: 1,\n" .
"                titleColor: '#f1f5f9',\n" .
"                bodyColor: '#94a3b8',\n" .
"                padding: 12,\n" .
"                callbacks: {\n" .
"                    label: c => ' ' + c.dataset.label + ': \$' + Number(c.parsed.y).toLocaleString('es-CO')\n" .
"                }\n" .
"            }\n" .
"        },\n" .
"        scales: {\n" .
"            x: {\n" .
"                grid: { color: 'rgba(255,255,255,.04)', drawBorder: false },\n" .
"                ticks: { color: '#94a3b8', font: { size: 12 } }\n" .
"            },\n" .
"            y: {\n" .
"                grid: { color: 'rgba(255,255,255,.04)', drawBorder: false },\n" .
"                ticks: {\n" .
"                    color: '#94a3b8', font: { size: 11 },\n" .
"                    callback: v => '\$' + (v>=1000000?(v/1000000).toFixed(1)+'M':v>=1000?(v/1000).toFixed(0)+'K':v)\n" .
"                }\n" .
"            }\n" .
"        }\n" .
"    }\n" .
"});\n" .
"</script>\n" .
"</body>\n" .
"</html>\n";

$newContent = substr($content, 0, $cutPos) . $phpTail;
file_put_contents($file, $newContent);
echo "Fixed! New file size: " . strlen($newContent) . " bytes, lines: " . substr_count($newContent, "\n");

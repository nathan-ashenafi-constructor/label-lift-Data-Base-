<?php
date_default_timezone_set($config['timezone']);

if (!file_exists($config['output_dir'])) {
    mkdir($config['output_dir'], 0755, true);
}

class ApacheLogAnalyzer {
    private $accessData = [];
    private $errorData = [];
    private $pageStats = [];
    private $browserStats = [];
    private $ipStats = [];
    private $errorStats = [];
    private $config;
    
    public function __construct($config) {
        $this->config = $config;
    }
    
    public function parseAccessLog($logFile) {
        if (!file_exists($logFile)) die("Access log file not found: $logFile\n");
        $handle = fopen($logFile, 'r');
        if (!$handle) die("Cannot open access log file: $logFile\n");
        
        while (($line = fgets($handle)) !== false) {
            $pattern = '/^(\S+)\s+\S+\s+\S+\s+\[([^\]]+)\]\s+"(\S+)\s+([^\s"]+)\s+([^"]+)"\s+(\d{3})\s+(\d+|-)\s+"([^"]*)"\s+"([^"]*)"$/';
            if (preg_match($pattern, $line, $matches)) {
                $entry = [
                    'ip' => $matches[1],
                    'timestamp' => $this->parseApacheTimestamp($matches[2]),
                    'method' => $matches[3],
                    'path' => $matches[4],
                    'protocol' => $matches[5],
                    'status' => $matches[6],
                    'size' => $matches[7] === '-' ? 0 : intval($matches[7]),
                    'referer' => $matches[8],
                    'user_agent' => $matches[9],
                    'browser' => $this->extractBrowser($matches[9])
                ];
                $this->accessData[] = $entry;
                $this->updateAccessStats($entry);
            }
        }
        fclose($handle);
    }
    
    public function parseErrorLog($logFile) {
        if (!file_exists($logFile)) return;
        $handle = fopen($logFile, 'r');
        if (!$handle) return;
        
        while (($line = fgets($handle)) !== false) {
            $pattern = '/^\[([^\]]+)\]\s+\[([^\]]+)\]\s+(?:\[pid\s+\d+\]\s+)?(?:\[client\s+([^\]]+)\]\s+)?(.+)$/';
            if (preg_match($pattern, $line, $matches)) {
                $clientInfo = isset($matches[3]) ? $matches[3] : 'unknown';
                $ip = 'unknown';
                if (preg_match('/^([\d\.]+)/', $clientInfo, $ipMatch)) $ip = $ipMatch[1];
                $entry = [
                    'timestamp' => $this->parseErrorTimestamp($matches[1]),
                    'level' => $matches[2],
                    'client' => $clientInfo,
                    'ip' => $ip,
                    'message' => $matches[4]
                ];
                $this->errorData[] = $entry;
                $this->updateErrorStats($entry);
            }
        }
        fclose($handle);
    }
    
    private function updateAccessStats($entry) {
        $page = $entry['path'];
        $ip = $entry['ip'];
        $browser = $entry['browser'];
        if (!isset($this->pageStats[$page])) {
            $this->pageStats[$page] = ['count' => 0, 'ips' => [], 'timestamps' => [], 'browsers' => []];
        }
        $this->pageStats[$page]['count']++;
        $this->pageStats[$page]['ips'][] = $ip;
        $this->pageStats[$page]['timestamps'][] = $entry['timestamp'];
        $this->pageStats[$page]['browsers'][] = $browser;
        if (!isset($this->ipStats[$ip])) $this->ipStats[$ip] = 0;
        $this->ipStats[$ip]++;
        if (!isset($this->browserStats[$browser])) $this->browserStats[$browser] = 0;
        $this->browserStats[$browser]++;
    }
    
    private function updateErrorStats($entry) {
        $level = $entry['level'];
        $ip = $entry['ip'];
        if (!isset($this->errorStats[$level])) {
            $this->errorStats[$level] = ['count' => 0, 'ips' => [], 'timestamps' => []];
        }
        $this->errorStats[$level]['count']++;
        $this->errorStats[$level]['ips'][] = $ip;
        $this->errorStats[$level]['timestamps'][] = $entry['timestamp'];
    }
    
    private function parseApacheTimestamp($timestamp) {
        $dt = DateTime::createFromFormat('d/M/Y:H:i:s O', $timestamp);
        return $dt ? $dt->getTimestamp() : time();
    }
    
    private function parseErrorTimestamp($timestamp) {
        $timestamp = preg_replace('/\.\d+/', '', $timestamp);
        $dt = DateTime::createFromFormat('D M d H:i:s Y', $timestamp);
        return $dt ? $dt->getTimestamp() : time();
    }
    
    private function extractBrowser($userAgent) {
        if (empty($userAgent)) return 'Unknown';
        if (preg_match('/Firefox\/[\d\.]+/', $userAgent)) return 'Firefox';
        if (preg_match('/Chrome\/[\d\.]+/', $userAgent) && !preg_match('/Edge/', $userAgent)) return 'Chrome';
        if (preg_match('/Safari\/[\d\.]+/', $userAgent) && !preg_match('/Chrome/', $userAgent)) return 'Safari';
        if (preg_match('/Edge\/[\d\.]+/', $userAgent)) return 'Edge';
        if (preg_match('/MSIE|Trident/', $userAgent)) return 'Internet Explorer';
        if (preg_match('/bot|crawler|spider|scraper/i', $userAgent)) return 'Bot/Crawler';
        return 'Other';
    }
    
    public function generateReport() {
        $outputFile = $this->config['output_dir'] . 'report.html';
        $accessTimelineData = $this->prepareAccessTimelineData();
        $errorTimelineData = $this->prepareErrorTimelineData();
        arsort($this->pageStats);
        arsort($this->ipStats);
        arsort($this->browserStats);
        $accessTimelineJSON = json_encode($accessTimelineData);
        $errorTimelineJSON = json_encode($errorTimelineData);
        $browserDataJSON = json_encode($this->prepareBrowserChartData());
        $html = $this->buildReportHTML($accessTimelineJSON, $errorTimelineJSON, $browserDataJSON);
        file_put_contents($outputFile, $html);
    }
    
    private function buildReportHTML($accessTimelineJSON, $errorTimelineJSON, $browserDataJSON) {
        ob_start();
        ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Apache Log Analysis Report</title>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/date-fns@2.29.3/index.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-adapter-date-fns@3.0.0/dist/chartjs-adapter-date-fns.bundle.min.js"></script>
<style>
body{font-family:Arial,sans-serif;margin:20px;background:#f5f5f5;}
.container{max-width:1400px;margin:0 auto;background:white;padding:30px;border-radius:10px;}
h1{color:#333;border-bottom:3px solid #007bff;padding-bottom:10px;}
h2{color:#555;margin-top:30px;border-bottom:1px solid #ddd;padding-bottom:5px;}
table{width:100%;border-collapse:collapse;margin:20px 0;}
th,td{padding:10px;text-align:left;border-bottom:1px solid #ddd;}
th{background:#007bff;color:white;}
tr:hover{background:#f5f5f5;}
.chart-container{height:400px;margin:30px 0;}
</style>
</head>
<body>
<div class="container">
<h1>Apache Log Analysis Report</h1>
<p>Generated on: <?= $this->getCurrentTimestamp() ?></p>
<h2>Access Timeline</h2>
<div class="chart-container"><canvas id="accessTimeline"></canvas></div>
<h2>Error Timeline</h2>
<div class="chart-container"><canvas id="errorTimeline"></canvas></div>
<h2>Browser Distribution</h2>
<canvas id="browserChart"></canvas>
</div>
<script>
const accessData = <?= $accessTimelineJSON ?>;
const errorData = <?= $errorTimelineJSON ?>;
const browserData = <?= $browserDataJSON ?>;
new Chart(document.getElementById('accessTimeline'),{type:'line',data:{datasets:[{label:'Access',data:accessData,borderColor:'rgb(75,192,192)'}]},options:{responsive:true,scales:{x:{type:'time'},y:{beginAtZero:true}}}});
new Chart(document.getElementById('errorTimeline'),{type:'line',data:{datasets:[{label:'Errors',data:errorData,borderColor:'rgb(255,99,132)'}]},options:{responsive:true,scales:{x:{type:'time'},y:{beginAtZero:true}}}});
new Chart(document.getElementById('browserChart'),{type:'doughnut',data:browserData,options:{responsive:true}});
</script>
</body>
</html>
<?php
        return ob_get_clean();
    }
    
    private function prepareAccessTimelineData() {
        $timeline = [];
        foreach ($this->accessData as $entry) {
            $hour = floor($entry['timestamp'] / 3600) * 3600;
            $key = date('Y-m-d H:00:00', $hour);
            if (!isset($timeline[$key])) $timeline[$key] = 0;
            $timeline[$key]++;
        }
        $chartData = [];
        foreach ($timeline as $date => $count) $chartData[] = ['x' => $date, 'y' => $count];
        usort($chartData, fn($a,$b)=>strtotime($a['x'])-strtotime($b['x']));
        return $chartData;
    }
    
    private function prepareErrorTimelineData() {
        $timeline = [];
        foreach ($this->errorData as $entry) {
            $hour = floor($entry['timestamp'] / 3600) * 3600;
            $key = date('Y-m-d H:00:00', $hour);
            if (!isset($timeline[$key])) $timeline[$key] = 0;
            $timeline[$key]++;
        }
        $chartData = [];
        foreach ($timeline as $date => $count) $chartData[] = ['x' => $date, 'y' => $count];
        usort($chartData, fn($a,$b)=>strtotime($a['x'])-strtotime($b['x']));
        return $chartData;
    }
    
    private function prepareBrowserChartData() {
        $labels = [];
        $data = [];
        foreach ($this->browserStats as $browser => $count) {
            $labels[] = $browser;
            $data[] = $count;
        }
        return ['labels' => $labels, 'datasets' => [['data' => $data]]];
    }
    
    private function getCurrentTimestamp() {
        return date('Y-m-d H:i:s');
    }
    
    public function exportToCSV() {
        $csvFile = $this->config['output_dir'] . 'access_stats.csv';
        $fp = fopen($csvFile, 'w');
        fputcsv($fp, ['Page','IP','Timestamp','Browser','Status','Method']);
        foreach ($this->accessData as $entry) {
            fputcsv($fp, [$entry['path'],$entry['ip'],date('Y-m-d H:i:s',$entry['timestamp']),$entry['browser'],$entry['status'],$entry['method']]);
        }
        fclose($fp);
        if (!empty($this->errorData)) {
            $csvFile = $this->config['output_dir'] . 'error_stats.csv';
            $fp = fopen($csvFile, 'w');
            fputcsv($fp, ['Timestamp','Level','IP','Message']);
            foreach ($this->errorData as $entry) {
                fputcsv($fp, [date('Y-m-d H:i:s',$entry['timestamp']),$entry['level'],$entry['ip'],substr($entry['message'],0,200)]);
            }
            fclose($fp);
        }
    }
}

$options = getopt("a:e:o:g:u:h", ["access:", "error:", "output:", "generate:", "url:", "help"]);

if (isset($options['h']) || isset($options['help'])) exit(0);
if (isset($options['a']) || isset($options['access'])) $config['access_log'] = $options['a'] ?? $options['access'];
if (isset($options['e']) || isset($options['error'])) $config['error_log'] = $options['e'] ?? $options['error'];
if (isset($options['o']) || isset($options['output'])) $config['output_dir'] = rtrim($options['o'] ?? $options['output'], '/') . '/';

$analyzer = new ApacheLogAnalyzer($config);
$analyzer->parseAccessLog($config['access_log']);
$analyzer->parseErrorLog($config['error_log']);
$analyzer->generateReport();
$analyzer->exportToCSV();
?>

<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$manifestPath = $root . '/BUILD_VERIFICATION.json';
$manifest = json_decode((string) file_get_contents($manifestPath), true, 512, JSON_THROW_ON_ERROR);
$transcript = array(
    'schema_version' => 1,
    'generated_at' => gmdate('c'),
    'commands' => array(),
);
$passed = true;

foreach ($manifest['commands'] as $command) {
    $definition = $root . '/' . $command['definition_file'];
    if (!is_file($definition) || empty($command['argv'])) {
        throw new RuntimeException('Invalid verification command definition for ' . $command['name']);
    }

    $escaped = array_map('escapeshellarg', $command['argv']);
    $process = proc_open(
        implode(' ', $escaped),
        array(1 => array('pipe', 'w'), 2 => array('pipe', 'w')),
        $pipes,
        $root
    );
    if (!is_resource($process)) {
        throw new RuntimeException('Unable to start verification command ' . $command['name']);
    }

    $stdout = stream_get_contents($pipes[1]);
    $stderr = stream_get_contents($pipes[2]);
    fclose($pipes[1]);
    fclose($pipes[2]);
    $exitCode = proc_close($process);

    $transcript['commands'][] = array(
        'name' => $command['name'],
        'argv' => $command['argv'],
        'definition_file' => $command['definition_file'],
        'exit_code' => $exitCode,
        'stdout' => $stdout,
        'stderr' => $stderr,
    );
    $passed = $passed && $exitCode === 0;
}

$transcript['passed'] = $passed;
$evidenceDir = $root . '/evidence/verification';
if (!is_dir($evidenceDir) && !mkdir($evidenceDir, 0775, true) && !is_dir($evidenceDir)) {
    throw new RuntimeException('Unable to create the verification evidence directory.');
}
file_put_contents(
    $evidenceDir . '/command-transcript.json',
    json_encode($transcript, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL
);

echo $passed ? "Build verification passed.\n" : "Build verification failed.\n";
exit($passed ? 0 : 1);

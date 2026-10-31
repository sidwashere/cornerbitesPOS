<?php
$dst = '/home/mllbontz/home/mllbontz/pos.cornerbites.co.ke/index.html';
$chunk_num = isset($_POST['chunk_num']) ? intval($_POST['chunk_num']) : -1;
$total_chunks = isset($_POST['total_chunks']) ? intval($_POST['total_chunks']) : 0;
$chunk_data = isset($_POST['chunk_data']) ? $_POST['chunk_data'] : '';

if ($chunk_num < 0 || $total_chunks <= 0 || empty($chunk_data)) {
    echo json_encode(['ok' => false, 'error' => 'missing params']);
    exit;
}

$tmp = '/home/mllbontz/home/mllbontz/pos.cornerbites.co.ke/.index_tmp';
file_put_contents($tmp, $chunk_data, FILE_APPEND | LOCK_EX);

if ($chunk_num == $total_chunks - 1) {
    rename($tmp, $dst);
    echo json_encode(['ok' => true, 'done' => true, 'size' => filesize($dst)]);
} else {
    echo json_encode(['ok' => true, 'done' => false, 'chunk' => $chunk_num]);
}

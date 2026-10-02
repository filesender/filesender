<?php

function file_size_to_offset_map_size($file_size, $chunk_size) {
    $number_of_chunks = ceil($file_size / $chunk_size);
    
    $offset_map_size = 0;
    
    for($n=1; $n<=$number_of_chunks; $n++) {
        $offset_map_size += log10($chunk_size * $n) + 2;
    }
    
    return $offset_map_size + 2;
}

function sizeToBytes($size) {
    $multipliers = array('', 'k', 'M', 'G', 'T');
    
    $pow = floor(($size ? log($size) : 0) / log(1024));
    $pow = min($pow, count($multipliers) - 1);
    
    $size /= pow(1024, $pow);
    
    return round($size, 1).' '.$multipliers[$pow].'b';
}


$chunk_sizes = array(1, 2, 5, 10); // Mb

$file_sizes = array(0.1, 1, 2, 5, 10, 20, 50, 100, 200, 500, 1000); // Gb

echo 'file_size (Gb) \\ chunk_size (Mb);'.implode(';', $chunk_sizes)."\n";

foreach($file_sizes as $file_size) {
    echo $file_size;
    flush();
    
    foreach($chunk_sizes as $chunk_size) {
        $size = file_size_to_offset_map_size($file_size * 1024 * 1024 * 1024, $chunk_size * 1024 * 1024);
        
        echo ';'.sizeToBytes($size);
        flush();
    }
    
    echo "\n";
}

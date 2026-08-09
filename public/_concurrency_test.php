<?php
// Simula un webhook "en vuelo": duerme 8s (como el LLM) y responde.
sleep(8);
echo json_encode(["status" => "ok", "worker" => getmypid()]);

<?php
declare(strict_types=1);

// Keep the root URL convenient while preserving the requested generate.php path.
header('Location: /generate.php', true, 302);

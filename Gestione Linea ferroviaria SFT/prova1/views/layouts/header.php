<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SFT - Sistema Ferroviario Turistico</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="index.php">SFT</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <?php if (Session::isLoggedIn() && Session::getUserType() !== 'admin' && Session::getUserType() !== 'esercente'): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="index.php?page=trains">Orari</a>
                        </li>
                    <?php endif; ?>
                </ul>
                <ul class="navbar-nav">
                    <?php if (Session::isLoggedIn()): ?>
                        <?php if (Session::getUserType() === 'admin'): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="index.php?page=backoffice&action=admin">
                                    <i class="fas fa-cogs"></i> Backoffice
                                </a>
                            </li>
                        <?php endif; ?>
                        
                        <li class="nav-item">
                            <span class="nav-link">
                                <i class="fas fa-user"></i> 
                                <?php echo htmlspecialchars((string)Session::getUserEmail()) ?>
                            </span>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="index.php?page=auth&action=logout">
                                <i class="fas fa-sign-out-alt"></i> Logout
                            </a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="index.php?page=auth&action=login">
                                <i class="fas fa-sign-in-alt"></i> Login
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="index.php?page=auth&action=register">
                                <i class="fas fa-user-plus"></i> Registrati
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
    <?php 
    $errorMsg = Session::getErrorMessage();
    $successMsg = Session::getSuccessMessage();
    if($errorMsg || $successMsg): 
?>
    <div class="alert <?php echo $errorMsg ? 'alert-danger' : 'alert-success'; ?> alert-dismissible fade show">
        <?php echo htmlspecialchars((string)($errorMsg ?: $successMsg)); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>
    </div>
<div class="row">
    <div class="col-md-8">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">Il tuo profilo</h5>
            </div>
            <div class="card-body">
                <?php if(isset($_SESSION['success'])): ?>
                    <div class="alert alert-success">
                        <?php 
                            echo $_SESSION['success'];
                            unset($_SESSION['success']);
                        ?>
                    </div>
                <?php endif; ?>

                <?php if(isset($_SESSION['error'])): ?>
                    <div class="alert alert-danger">
                        <?php 
                            echo $_SESSION['error'];
                            unset($_SESSION['error']);
                        ?>
                    </div>
                <?php endif; ?>

                <form action="index.php?page=profile&action=update" method="POST">
                    <div class="mb-3">
                        <label class="form-label">Nome</label>
                        <input type="text" class="form-control" name="nome" 
                               value="<?php echo htmlspecialchars($user['nome']); ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Cognome</label>
                        <input type="text" class="form-control" name="cognome" 
                               value="<?php echo htmlspecialchars($user['cognome']); ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-control" name="email" 
                               value="<?php echo htmlspecialchars($user['email']); ?>" required>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Aggiorna profilo</button>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Cambia password</h5>
            </div>
            <div class="card-body">
                <form action="index.php?page=profile&action=changePassword" method="POST">
                    <div class="mb-3">
                        <label class="form-label">Password attuale</label>
                        <input type="password" class="form-control" name="current_password" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Nuova password</label>
                        <input type="password" class="form-control" name="new_password" required>
                        <div class="form-text">La password deve essere di almeno 8 caratteri.</div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Conferma nuova password</label>
                        <input type="password" class="form-control" name="confirm_password" required>
                    </div>
                    
                    <button type="submit" class="btn btn-warning">Cambia password</button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">Informazioni account</h5>
            </div>
            <div class="card-body">
                <p><strong>Tipo account:</strong> <?php echo ucfirst($user['tipo']); ?></p>
                <p><strong>Data registrazione:</strong> <?php echo date('d/m/Y', strtotime($user['created_at'])); ?></p>
                <p><strong>Saldo disponibile:</strong> €<?php echo number_format($account['saldo'], 2); ?></p>
                <a href="index.php?page=account" class="btn btn-outline-primary">Gestisci conto</a>
            </div>
        </div>

        <?php if($user['tipo'] === 'registrato'): ?>
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Statistiche</h5>
                </div>
                <div class="card-body">
                    <p><strong>Biglietti acquistati:</strong> <?php echo $stats['total_tickets'] ?? 0; ?></p>
                    <p><strong>Ultimo viaggio:</strong> <?php echo $stats['last_trip'] ?? 'Nessun viaggio'; ?></p>
                    <a href="index.php?page=tickets" class="btn btn-outline-primary">I miei biglietti</a>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php

case 'profile':
    require_once 'controllers/ProfileController.php';
    $controller = new ProfileController();
    if(method_exists($controller, $action)) {
        $controller->$action();
    } else {
        $controller->index();
    }
    break;
?>
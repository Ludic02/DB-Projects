<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3>Registrazione</h3>
                </div>
                <div class="card-body">
                    <?php if(isset($_SESSION['error_message'])): ?>
                        <div class="alert alert-danger">
                            <?php 
                                echo $_SESSION['error_message']; 
                                unset($_SESSION['error_message']);
                            ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="index.php?page=auth&action=register">
                        <div class="mb-3">
                            <label for="nome" class="form-label">Nome</label>
                            <input type="text" class="form-control" id="nome" name="nome" required>
                        </div>
                        <div class="mb-3">
                            <label for="cognome" class="form-label">Cognome</label>
                            <input type="text" class="form-control" id="cognome" name="cognome" required>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Registrati</button>
                        <a href="index.php?page=auth&action=login" class="btn btn-link">Torna al login</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
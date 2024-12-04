<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3>Login</h3>
                </div>
                <div class="card-body">
                    <?php if(isset($_SESSION['error_message'])): ?>
                        <div class="alert alert-danger">
                            <?php 
                                echo htmlspecialchars($_SESSION['error_message']); 
                                unset($_SESSION['error_message']);
                            ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="index.php?page=auth&action=login">
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Login</button>
                        <a href="index.php?page=auth&action=register" class="btn btn-link">Registrati</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
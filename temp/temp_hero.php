<!-- Hero Section Moderno Unificado -->
<div class="card border-0 mb-4 overflow-hidden bg-primary shadow-lg rounded-3 animate-fade-in-up">
    <div class="hero-wrapper position-relative">
        <!-- Background com gradiente e padrão -->
        <div class="hero-bg position-absolute w-100 h-100"
            style="background: linear-gradient(135deg, #1a73e8 0%, #0d47a1 100%);
            background-image: url('assets/images/pattern.png'), radial-gradient(circle at 1px 1px, rgba(255,255,255,0.15) 1px, transparent 0);
            background-size: cover, 20px 20px;">
        </div>

        <!-- Conteúdo do Hero -->
        <div class="card-body position-relative py-4 text-white">
            <div class="row align-items-center">
                <!-- Coluna da Logo e Informações do Usuário -->
                <div class="col-lg-8">
                    <div class="d-flex align-items-center flex-column flex-md-row">
                        <div class="logo-container bg-white rounded-4 p-3 d-inline-block shadow-lg me-md-4 mb-3 mb-md-0">
                            <img src="assets/images/logo.png"
                                alt="SUPACO Logo"
                                class="img-fluid rounded-4"
                                style="width: 100px; height: 100px; object-fit: cover;">
                        </div>
                        <div class="ms-0 ms-md-3 text-center text-md-start">
                            <div class="d-flex align-items-center mb-3">
                                <?php if (isset($meusDados['url_foto_75x100'])): ?>
                                    <img src="<?php echo htmlspecialchars($meusDados['url_foto_75x100']); ?>" alt="Foto de perfil" class="rounded-circle border border-3 border-white shadow me-3" style="width: 60px; height: 60px; object-fit: cover;">
                                <?php else: ?>
                                    <div class="rounded-circle bg-white text-primary d-flex align-items-center justify-content-center shadow me-3" style="width: 60px; height: 60px;">
                                        <i class="fas fa-user-graduate fa-2x"></i>
                                    </div>
                                <?php endif; ?>
                                <div>
                                    <h2 class="h4 mb-0">
                                        <?php echo isset($meusDados['nome_usual']) ? htmlspecialchars($meusDados['nome_usual']) : 'Estudante'; ?>
                                    </h2>
                                    <div class="badge bg-success mt-1">
                                        <i class="fas fa-check-circle me-1"></i>
                                        <?php echo isset($meusDados['vinculo']['situacao']) ? htmlspecialchars($meusDados['vinculo']['situacao']) : 'Estudante Ativo'; ?>
                                    </div>
                                </div>
                            </div>
                            <div class="badges-wrapper">
                                <span class="badge bg-white text-primary me-2">
                                    <i class="fas fa-id-card me-1"></i>
                                    <?php echo isset($meusDados['matricula']) ? htmlspecialchars($meusDados['matricula']) : ''; ?>
                                </span>
                                <?php if (isset($meusDados['vinculo']['curso'])): ?>
                                    <span class="badge bg-white text-primary">
                                        <i class="fas fa-graduation-cap me-1"></i>
                                        <?php echo htmlspecialchars($meusDados['vinculo']['curso']); ?>
                                    </span>
                                <?php endif; ?>
                                <div class="mt-2">
                                    <span class="badge bg-white text-primary">
                                        <i class="fas fa-school me-1"></i>
                                        <?php echo isset($meusDados['vinculo']['campus']) ? htmlspecialchars($meusDados['vinculo']['campus']) : 'Campus'; ?>
                                    </span>
                                    <span class="badge bg-white text-primary">
                                        <i class="fas fa-calendar-alt me-1"></i>
                                        Período <?php echo $anoLetivo; ?>.<?php echo $periodoLetivo; ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Coluna do Conteúdo -->
                <div class="col-lg-4 mt-4 mt-lg-0 text-white text-center text-lg-end">
                    <div class="text-shadow">
                        <h2 class="h3 mb-2">
                            SUPACO
                            <span class="badge bg-white text-primary fs-6 align-middle ms-1">Beta</span>
                        </h2>
                        <p class="mb-2">
                            <em>Sistema Útil Pra Aluno Cansado e Ocupado</em>
                        </p>
                        <div class="d-flex flex-column flex-sm-row justify-content-center justify-content-lg-end mt-2 gap-2">
                            <a href="index.php" class="btn btn-light btn-sm">
                                <i class="fas fa-home me-1"></i> Dashboard
                            </a>
                            <a href="logout.php" class="btn btn-outline-light btn-sm">
                                <i class="fas fa-sign-out-alt me-1"></i> Sair
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-footer bg-primary-dark py-2 border-top border-primary-dark">
            <div class="row text-center">
                <div class="col-4">
                    <small>
                        <i class="fas fa-graduation-cap me-1"></i> Disciplinas:
                        <strong><?php echo isset($boletim) ? count($boletim) : '0'; ?></strong>
                    </small>
                </div>
                <div class="col-4">
                    <small>
                        <i class="fas fa-calendar-check me-1"></i> Aulas Hoje:
                        <strong>
                            <?php
                            $aulasHoje = isset($horarios) ? count(getAulasHoje($horarios)) : 0;
                            echo $aulasHoje;
                            ?>
                        </strong>
                    </small>
                </div>
                <div class="col-4">
                    <small>
                        <i class="fas fa-clock me-1"></i> Última atualização:
                        <strong><?php echo date('H:i'); ?></strong>
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>
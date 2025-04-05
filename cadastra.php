<body>
    <h1>Cadastro de Aluno</h1>
    <form action="#" method="post">
        <div>
            <label for="nome" class="required">Nome Completo</label>
            <input type="text" id="nome" name="nome" required>
        </div>

        <div>
            <label for="data_nasc" class="required">Data de Nascimento</label>
            <input type="date" id="data_nasc" name="data_nasc" required>
        </div>

        <div>
            <label for="cpf" class="required">CPF</label>
            <input type="text" id="cpf" name="cpf" required placeholder="000.000.000-00">
        </div>

        <div>
            <label for="email" class="required">E-mail</label>
            <input type="email" id="email" name="email" required>
        </div>

        <div>
            <label for="telefone">Telefone</label>
            <input type="tel" id="telefone" name="telefone" placeholder="(00) 00000-0000">
        </div>

        <div>
            <label for="endereco">Endereço</label>
            <input type="text" id="endereco" name="endereco">
        </div>

        <div>
            <label for="curso" class="required">Curso de Interesse #(exemplo)</label>
            <select id="curso" name="curso" required>
                <option value="">Selecione um curso</option>
                <option value="informatica">Informática Básica</option>
                <option value="ingles">Inglês</option>
                <option value="design">Design Gráfico</option>
                <option value="programacao">Programação</option>
                <option value="gestao">Gestão Empresarial</option>
            </select>
        </div>


        <button type="submit">Cadastrar Aluno</button>
    </form>
</body>

</html>
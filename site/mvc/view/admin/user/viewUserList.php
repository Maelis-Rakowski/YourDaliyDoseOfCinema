<?php $this->_t="UserList"?>
<div class="container p-5">
    <table class="table table-striped" data-element-type="user">
        <tr>
            <th>Pseudo</th>
            <th>Email</th>
            <th>Admin</th>
            <th>Modification</th>
            <th>Suppression</th>
        </tr>
        
        <?php foreach ($users as $obj): ?>
            <tr id=<?= htmlspecialchars($obj->getId()) ?>>
                <td><?= htmlspecialchars($obj->getPseudo()) ?></td>
                <td><?= htmlspecialchars($obj->getEmail()) ?></td>
                <td><?= htmlspecialchars($obj->getIsAdmin()) ? "true" : "false" ?></td>
                <td>
                    <form method="POST" action="/admin/user/edit">
                        <input type="hidden" name="user_id"         value = "<?= htmlspecialchars($obj->getId())            ?>">
                        <input type="hidden" name="user_email"      value = "<?= htmlspecialchars($obj->getEmail())         ?>">
                        <input type="hidden" name="user_pseudo"     value = "<?= htmlspecialchars($obj->getPseudo())        ?>">
                        <input type="hidden" name="user_isAdmin"    value = "<?= htmlspecialchars($obj->getIsAdmin())       ?>">

                        <input type=<?=$obj->getPseudo() == $_SESSION['pseudo'] ? "hidden" : "submit" ?> value="Modify">
                    </form>
                </td>
                <td>
                    <button class="btn btn-danger delete-button" data-id="<?= htmlspecialchars($obj->getId()) ?>">Delete</button>
                </td>
            </tr>
        <?php endforeach;?>
    </table>
</div>
<script src="/scripts/data_list.js"></script>
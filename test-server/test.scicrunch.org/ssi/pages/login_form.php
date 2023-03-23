<form method="post" class="sky-form" style="border:0" action="/forms/login.php">
    <?php if(!is_null($msg)): ?>
        <fieldset>
            <section>
                <p><?php echo $msg ?></p>
            </section>
        </fieldset>
    <?php endif ?>
    <fieldset>
        <section>
            <div class="input">
                <i class="fa fa-envelope icon-prepend" style="top: 1px;height: 32px;font-size: 14px;line-height: 33px;background: inherit;color:#b3b3b3;background:#fff;left:1px;padding-left:6px;"></i>
                <input type="text" class="form-control" name="email" placeholder="Email">
            </div>
        </section>
        <div class="input">
            <i class="fa fa-lock icon-prepend" style="top: 1px;height: 32px;font-size: 14px;line-height: 33px;background: inherit;color:#b3b3b3;background:#fff;left:1px;padding-left:6px;"></i>
            <input type="password" class="form-control" name="password" placeholder="Password">
        </div>
        <hr style="margin:18px 0">

        <div class="row">
            <div class="col-md-10 col-md-offset-1">
                <button type="submit" class="btn-u btn-block">Log In</button>
            </div>
        </div>
    </fieldset>
</form>

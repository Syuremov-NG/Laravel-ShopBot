<!DOCTYPE html>
<html lang="">
<head>
    <title>Login</title>
</head>
<link rel="stylesheet" href="assets/css/auth.css?v={{time()}}" />
<script src="https://telegram.org/js/telegram-web-app.js"></script>
<script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
<body class="login-page">
    <form id="form" class="form login-form">
        <div class="login">Login</div>
        @csrf
        <div>
            <input name="email" id="email" type="email" placeholder="email"  value="{{ old('email') }}" required autofocus>
            <input name="password" id="password" type="password" placeholder="password" required autocomplete="current-password"/>
            <button>login</button>
        </div>
    </form>
    <div class="loader loader--style6" title="5">
        <svg version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
             width="24px" height="30px" viewBox="0 0 24 30" style="enable-background:new 0 0 50 50;" xml:space="preserve">
    <rect x="0" y="13" width="4" height="5" fill="#333">
        <animate attributeName="height" attributeType="XML"
                 values="5;21;5"
                 begin="0s" dur="0.6s" repeatCount="indefinite" />
        <animate attributeName="y" attributeType="XML"
                 values="13; 5; 13"
                 begin="0s" dur="0.6s" repeatCount="indefinite" />
    </rect>
            <rect x="10" y="13" width="4" height="5" fill="#333">
                <animate attributeName="height" attributeType="XML"
                         values="5;21;5"
                         begin="0.15s" dur="0.6s" repeatCount="indefinite" />
                <animate attributeName="y" attributeType="XML"
                         values="13; 5; 13"
                         begin="0.15s" dur="0.6s" repeatCount="indefinite" />
            </rect>
            <rect x="20" y="13" width="4" height="5" fill="#333">
                <animate attributeName="height" attributeType="XML"
                         values="5;21;5"
                         begin="0.3s" dur="0.6s" repeatCount="indefinite" />
                <animate attributeName="y" attributeType="XML"
                         values="13; 5; 13"
                         begin="0.3s" dur="0.6s" repeatCount="indefinite" />
            </rect>
  </svg>
    </div>
</body>
<script>
    const url = new URL(window.location.href);
    let $loading = $('.loader').hide();
    $(document)
        .ajaxStart(function () {
            $loading.show();
        })
        .ajaxStop(function () {
            $loading.hide();
        });
    let tg = window.Telegram.WebApp;
    tg.expand();

    $("#form").submit(function (e) {
        $.ajax({
            type: "POST",
            url: "{{config('global.url')}}/api/login?id=" + url.searchParams.get('id') + "&message=" + url.searchParams.get('message'),
            data: $(this).serialize(),
            success: function(resp) {
                tg.close();
            },
            error: function (xhr, exception) {
                let result = confirm("Incorrect email or password. \nDo you want to try again? " + exception);
                if (!result) {
                    tg.close();
                }
            }
        })
        return false;
    })
</script>
</html>

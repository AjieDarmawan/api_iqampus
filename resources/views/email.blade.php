<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    hai {{ $nama }} terima kasih telah daftar di kampus {{ $kampus }}, berikut ini akun untuk mengakses iQampuz kami di <a href="{{ $situs }}">{{ $situs }}</a><br>
    <table>
        <tr>
            <td>Email</td>
            <td>: {{ $email }}</td>
        </tr>
        <tr>
            <td>Password</td>
            <td>: {{ $password }}</td>
        </tr>
    </table>
</body>
</html>
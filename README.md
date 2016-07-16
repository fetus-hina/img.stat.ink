# img.stat.ink

stat.ink の画像配信用のやる気のないアプリケーションのようなものです。

自分以外が使うことは想定していません。


## 使い方

### 前提

- PHP 7.0 を必要とします。必要としないように書き直すのは簡単ですが、PHP 7.0 が入っているのでその構文を使っています。
- GD が必要です。GD は WebP サポートが組み込まれていることを前提にしていますが、なくても動きはすると思います。
- [Lepton 1.0](https://github.com/dropbox/lepton) が `/usr/bin/lepton` に存在することを前提にしています。
- このアプリケーションは遅いので、前段に varnish 等のリバースプロキシがいることを想定しています。いなくても動きはします。

### 準備

- `www` があるのと同じところに `src` というディレクトリを準備します。その中身は次のどちらかのファイルが大量にあることを前提にしています。文字数も含めてきっちり同じでないと動きません。
    - `/df/dfzvhc2fpjaitdc4bwv7xjwbra.jpg` のような JPEG ファイル
    - `/df/dfzvhc2fpjaitdc4bwv7xjwbra.lep` のような Lepton で圧縮されたファイル
- `http://example.com/df/dfzvhc2fpjaitdc4bwv7xjwbra.jpg` とアクセスされたら `www/index.php` が動くように php-fpm あたりを設定します。

## 動作

- リクエストとファイルの存在に応じて、JPEG ファイルをそのまま返すか、Lepton 形式を JPEG に戻してから返すかします。
- クライアントが WebP に対応している場合（で、GD が WebP 対応の場合）、WebP に変換してから返します。

## ライセンス

MIT License | Copyright (C) 2016 AIZAWA Hina

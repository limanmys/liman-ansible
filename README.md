# Liman Ansible Yönetim Eklentisi

Eklenti Liman MYS üzerinden ansible platformunu yönetmenizi sağlamaktadır. 

### Kurulum
Kurulum için Liman MYS üzerinden sunucunuza eklentiyi ekleyip "Paketleri depodan kur." butonunu kullanmanız yeterlidir. 

![Paket İndirme Ekranı](https://github.com/limanmys/liman-ansible/blob/master/screenshots/install-packages.JPG)

Kurulacak olan paketler ;
* ansible
* sshpass
* unzip

### Playbook Çalıştırma

Playbook kısmında playbook oluşturup sağ tık ile çalıştır diyerek grup seçebilirsiniz. Ardından playbook client makinelerde çalışmaya başlamaktadır. Playbook çıktısını log olarak isteğe bağlık şekilde kaydedebilirsiniz.

![Playbook Çıktısı](https://github.com/limanmys/liman-ansible/blob/master/screenshots/playbook-output.JPG)

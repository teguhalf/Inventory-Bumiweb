<?php
session_start();
//Membuat koneksi ke database
$conn = mysqli_connect("localhost", "root", "", "stokbarang");

//Menambah Barang
if(isset($_POST['addnewbarang'])){
    $namabarang = $_POST['namabarang'];
    $deskripsi = $_POST['deskripsi'];
    $stock = $_POST['stock'];

    //Gambar
    $allowed_extension = array('png', 'jpg', 'jpeg');
    $nama = $_FILES['file']['name']; //Mengambil Gambar
    $dot = explode('.', $nama);
    $ekstensi = strtolower(end($dot)); //Mengambil Ekstensi Filenya
    $ukuran = $_FILES['file']['size']; //Mengambil Size Filenya
    $file_tmp = $_FILES['file']['tmp_name']; //Mengambil Lokasi Filenya

    //Penamaan File => Enkripsi
    $image = md5(uniqid($nama,true) . time()).'.'.$ekstensi; //Menggabungkan nama file yang dienkripsi dg ekstensinya

    //Validasi Barang Sudah Terdaftar
    $cek = mysqli_query($conn, "select * from stock where namabarang='$namabarang'");
    $hitung = mysqli_num_rows($cek);

    if($hitung<1){
        //Jika Belum Terdaftar
        //Proses Upload Gambar
        if(in_array($ekstensi, $allowed_extension) === true){
            //Validasi Ukuran Filenya
            if($ukuran < 20000000){
                move_uploaded_file($file_tmp, 'images/'.$image);
                $addtotable = mysqli_query($conn, "insert into stock (namabarang, deskripsi, stock, image) values('$namabarang', '$deskripsi', '$stock', '$image')");
                if($addtotable){
                    header("location:index.php");
                } else {
                    echo 'Gagal';
                    header("location:index.php");
                }
            } else {
                //Kalau Filenya Melebihi 20mb
                echo '
                <script>
                    alert("UKURAN TERLALU BESAR")
                    window.location.href="index.php";
                </script>
                ';
            } 
        } 
    } else {
            //Kalau Barangnya Sudah Terdaftar
            echo '
            <script>
                alert("BARANG SUDAH TERDAFTAR DI STOCK")
                window.location.href="index.php";
            </script>
            ';
    }
};

//Menambah Barang Masuk
if(isset($_POST['barangmasuk'])){
    $barangnya = $_POST['barangnya'];
    $penerima = $_POST ['penerima'];
    $qty = $_POST['qty'];

    $cekstocksekarang = mysqli_query($conn, "select * from stock where idbarang='$barangnya'");
    $ambildatanya = mysqli_fetch_array($cekstocksekarang);

    $stocksekarang = $ambildatanya['stock'];
    $tambahkanstocksekarangdenganquantity = $stocksekarang+$qty;

    $addtomasuk = mysqli_query($conn, "insert into masuk (idbarang, keterangan, qty) values('$barangnya', '$penerima', '$qty')");
    $updatestockmasuk = mysqli_query($conn,"update stock set stock='$tambahkanstocksekarangdenganquantity' where idbarang='$barangnya'");
    if($addtomasuk&&$updatestockmasuk){
        header("location:masuk.php");
    } else {
        echo 'Gagal';
        header("location:masuk.php");
    }
}

//Menambah Barang Keluar
if(isset($_POST['addbarangkeluar'])){
    $barangnya = $_POST['barangnya'];
    $penerima = $_POST ['penerima'];
    $qty = $_POST['qty'];

    $cekstocksekarang = mysqli_query($conn, "select * from stock where idbarang='$barangnya'");
    $ambildatanya = mysqli_fetch_array($cekstocksekarang);

    $stocksekarang = $ambildatanya['stock'];
    
    if ($stocksekarang >= $qty){
        //Kalau Barangnya Cukup
        $tambahkanstocksekarangdenganquantity = $stocksekarang-$qty;
    
        $addtokeluar = mysqli_query($conn, "insert into keluar (idbarang, penerima, qty) values('$barangnya', '$penerima', '$qty')");
        $updatestockmasuk = mysqli_query($conn,"update stock set stock='$tambahkanstocksekarangdenganquantity' where idbarang='$barangnya'");
        
        if($addtokeluar&&$updatestockmasuk){
            header("location:keluar.php");
        } else {
            echo 'Gagal';
            header("location:keluar.php");
        }
    } else {
        //Kalau Barangnya Tidak Cukup
        echo '
        <script>
            alert("Stock Saat Ini Tidak Mencukupi")
            window.location.href="keluar.php";
        </script>
        ';
    }
}

//Update Info Barang
if(isset($_POST['updatebarang'])){
    $idb = $_POST['idb'];
    $namabarang = $_POST['namabarang'];
    $deskripsi = $_POST['deskripsi'];

    //Gambar
    $allowed_extension = array('png', 'jpg');
    $nama = $_FILES['file']['name']; //Mengambil Gambar
    $dot = explode('.', $nama);
    $ekstensi = strtolower(end($dot)); //Mengambil Ekstensi Filenya
    $ukuran = $_FILES['file']['size']; //Mengambil Size Filenya
    $file_tmp = $_FILES['file']['tmp_name']; //Mengambil Lokasi Filenya

    //Penamaan File => Enkripsi
    $image = md5(uniqid($nama,true) . time()).'.'.$ekstensi; //Menggabungkan nama file yang dienkripsi dg ekstensinya

    if($ukuran==0){
        //Jika tidak ingin upload gambar 
        $update = mysqli_query($conn,"update stock set namabarang='$namabarang', deskripsi='$deskripsi' where idbarang ='$idb'");
        if($update){
            header("location:index.php");
        } else {
            echo 'Gagal';
            header("location:index.php");
        }
    } else {
        //Jika ingin
        move_uploaded_file($file_tmp, 'images/'.$image);
        $update = mysqli_query($conn,"update stock set namabarang='$namabarang', deskripsi='$deskripsi', image='$image' where idbarang ='$idb'");
        if($update){
            header("location:index.php");
        } else {
            echo 'Gagal';
            header("location:index.php");
        }
    }
}

//Menghapus Barang dari stock
if(isset($_POST['hapusbarang'])){
    $idb = $_POST['idb'];

    $gambar = mysqli_query($conn, "select * from stock where idbarang='$idb'");
    $get = mysqli_fetch_array($gambar);
    $img = 'images/'.$get['image'];
    unlink($img);

    $hapus = mysqli_query($conn,"delete from stock where idbarang ='$idb'");
    if($hapus){
        header("location:index.php");
    } else {
        echo 'Gagal';
        header("location:index.php");
    }
}

//Mengubah Data Barang Masuk 
if(isset($_POST['updatebarangmasuk'])){
    $idb = $_POST['idb'];
    $idm = $_POST['idm'];
    $deskripsi = $_POST['keterangan'];
    $qty = $_POST['qty'];

    $lihatstock = mysqli_query($conn,"select * from stock where idbarang ='$idb'");
    $stocknya = mysqli_fetch_array($lihatstock);
    $stockskrg = $stocknya['stock'];

    $qtyskrg = mysqli_query($conn, "select * from masuk where idmasuk='$idm'");
    $qtynya = mysqli_fetch_array($qtyskrg);
    $qtyskrg = $qtynya['qty'];

    if($qty > $qtyskrg){
        $selisih = $qty - $qtyskrg;
        $kurangin = $stockskrg + $selisih;
        $updateStock = mysqli_query($conn, "update stock set stock='$kurangin' where idbarang='$idb'");
        $updateBarangMasuk = mysqli_query($conn, "update masuk set qty='$qty', keterangan='$deskripsi' where idmasuk='$idm'");
            if($updateStock && $updateBarangMasuk){
                header("location:masuk.php");
            } else {
                echo 'Gagal';
                header("location:masuk.php");
            }
    } else {
        $selisih = $qtyskrg - $qty;
        $kurangin = $stockskrg - $selisih;
        $updateStock = mysqli_query($conn, "update stock set stock='$kurangin' where idbarang='$idb'");
        $updateBarangMasuk = mysqli_query($conn, "update masuk set qty='$qty', keterangan='$deskripsi' where idmasuk='$idm'");
            if($updateStock && $updateBarangMasuk){
                header("location:masuk.php");
            } else {
                echo 'Gagal';
                header("location:masuk.php");
            }
    }
}

//Menghapus Barang Masuk
if(isset($_POST['hapusbarangmasuk'])){
    $idb = $_POST['idb'];
    $idm = $_POST['idm'];
    $qty = $_POST['kty'];

    $getdatastock = mysqli_query($conn,"select * from stock where idbarang ='$idb'");
    $data = mysqli_fetch_array($getdatastock);
    $stok = $data['stock'];

    $selisih = $stok-$qty;

    $update = mysqli_query($conn, "update stock set stock='$selisih' where idbarang='$idb'");
    $hapusdata = mysqli_query($conn, "delete from masuk where idmasuk='$idm'");

    if($update&&$hapusdata){
        header("location:masuk.php");
    } else {
        header("location:masuk.php");
    }
}   


//Mengubah Data Barang Keluar 
if(isset($_POST['updatebarangkeluar'])){
    $idb = $_POST['idb'];
    $idk = $_POST['idk'];
    $penerima = $_POST['penerima'];
    $qty = $_POST['qty'];

    $lihatstock = mysqli_query($conn,"select * from stock where idbarang ='$idb'");
    $stocknya = mysqli_fetch_array($lihatstock);
    $stockskrg = $stocknya['stock'];

    $qtyskrg = mysqli_query($conn, "select * from keluar where idkeluar='$idk'");
    $qtynya = mysqli_fetch_array($qtyskrg);
    $qtyskrg = $qtynya['qty'];

    if($qty > $qtyskrg){
        $selisih = $qty - $qtyskrg;
        $kurangin = $stockskrg - $selisih;
        //Mengubah Data Stock
        $kurangistocknya = mysqli_query($conn, "update stock set stock='$kurangin' where idbarang='$idb'");
        $updatenya = mysqli_query($conn, "update keluar set qty='$qty', penerima='$penerima' where idkeluar='$idk'");
            if($kurangistocknya && $updatenya){
                header("location:keluar.php");
            } else {
                echo 'Gagal';
                header("location:keluar.php");
            }
    } else {
        $selisih = $qtyskrg - $qty;
        $kurangin = $stockskrg + $selisih;
        $updateStock = mysqli_query($conn, "update stock set stock='$kurangin' where idbarang='$idb'");
        $updatenya = mysqli_query($conn, "update keluar set qty='$qty', penerima='$penerima' where idkeluar='$idk'");
            if($kurangistocknya && $updatenya){
                header("location:keluar.php");
            } else {
                echo 'Gagal';
                header("location:keluar.php");
            }
    }
}

//Menghapus Barang Keluar
if(isset($_POST['hapusbarangkeluar'])){
    $idb = $_POST['idb'];
    $idk = $_POST['idk'];
    $qty = $_POST['kty'];

    $getdatastock = mysqli_query($conn,"select * from stock where idbarang ='$idb'");
    $data = mysqli_fetch_array($getdatastock);
    $stok = $data['stock'];

    $selisih = $stok+$qty;

    $update = mysqli_query($conn, "update stock set stock='$selisih' where idbarang='$idb'");
    $hapusdata = mysqli_query($conn, "delete from keluar where idkeluar='$idk'");

    if($update&&$hapusdata){
        header("location:keluar.php");
    } else {
        header("location:keluar.php");
    }
} 


//Menambah Admin Baru
if(isset($_POST['addadmin'])){
    $email = $_POST['email'];
    $password = $_POST['password'];

    $queryinsert = mysqli_query($conn, "insert into login (email, password) values('$email', '$password')");

    if($queryinsert){
        header("location:admin.php");
    } else {
        header("location:admin.php");
    }
}

//Edit Email dan Password Admin
if(isset($_POST['updateadmin'])){
    $emailbaru = $_POST['emailadmin'];
    $passwordbaru = $_POST['passwordbaru'];
    $idnya = $_POST['id'];

    $queryupdate = mysqli_query($conn, "update login set email ='$emailbaru', password='$passwordbaru' where iduser='$idnya' ");
    if($queryupdate){
        header("location:admin.php");
    } else {
        header("location:admin.php");
    }
} 

//Hapus Admin
if(isset($_POST['hapusadmin'])){
    $id = $_POST['id'];

    $querydelete = mysqli_query($conn, "delete from login where iduser='$id'");
    if($querydelete){
        header("location:admin.php");
    } else {
        header("location:admin.php");
    }
}

?>
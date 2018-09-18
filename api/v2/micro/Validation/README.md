# Documentasi library Validation
Cara kerja library Validation , pada dasarnya library validation ini tujuannya adalah untuk menvalidasi data yang berkaitan dengan Inputan form dari client yang dikirim ke server 
***Berikut contoh singkat penggunaannya :***  

```php
    $data = array(
		"key_number"  => "123456789" , 
		"key_text"	  => "Ahmad Wahyudin",
		"key_email"	  => "wahyu@kct.co.id" ,
		"key_website" => "https://www.kct.co.id" ,
		"key_date"	  => "2018-03-03" 
	);
	// inisialisasi validation
    $validation = $this->validation->init($data);
    
    // validasi type number
    $validation->Number('key_number')->mandatory(true)->validate();
    
    // validasi type text
    $validation->Text('key_text')->mandatory(true)->minlen(10)->maxlen(20)->validate();
    
    // validasi email
    $validation->Email('key_email')->mandatory(true)->validate();
    
    // validasi url
    $validation->URL('key_website')->mandatory(true)->validate();
    
    // validasi date 
    $validation->Date('key_date')->delimiter("-")->format("Y-m-d")->validate();
    
    if ( $validation->hasError() ) {
	    return $validation->error_notify;
    } else {
	    // your statement here...
	}
```     
    

## Setting autoload library Validation 
Jika Object Library validation masih belum ada , maka bisa di tambahkan di /api/v2/app/{project}/config/app.php
tambahkan dibagian `'providers' => array( 'validation' => 'Micro\Validation\ValidationProvider' )` 


## Penggunaan Helper Validation

Helper Library validation meliputi

    Validation
	     ../
	     ./Date
	     ./Email
	     ./Entities
	     ./File
	     ./IdentityNumber
	     ./Ip
	     ./Number
	     ./Text
	     ./URL
	   

## Date Helper Object
```php
    $objValidation = $this->validation->init($data);
    $objValidation->Date('key')
    
    -> delimiter('/') // setting delimiter date seperti -,/ 
    -> format('Y-m-d') // setting format untuk menentukan posisi date month dan year 
    -> mandatory(true / false) // jika field mandatory atau tidak (default false)
    -> validate() // untuk eksekusi validasi
```  
 

## Email Helper Object
```php
    $objValidation = $this->validation->init($data);
    $objValidation->Email('key')
    
    -> mandatory(true / false) // jika field mandatory atau tidak (default false)
    -> validate() // untuk eksekusi validasi
```  

## Entities Helper Object

```php
    $objValidation = $this->validation->init($data);
    $objValidation->Entities('key')
    
    -> mandatory(true / false) // jika field mandatory atau tidak (default false)
    -> validate() // untuk eksekusi validasi
```  
 

## File Helper Object
Pelajari Mime type di [Sini](https://developer.mozilla.org/en-US/docs/Web/HTTP/Basics_of_HTTP/MIME_types/Complete_list_of_MIME_types)
```php
    $objValidation = $this->validation->init($ObjFile);
    $objValidation->Date('File')
    
    -> type(mime_type) // pelajari mimetype di 
    -> maxsize(kilobytes) // max size file tersebut
    -> validate() // untuk eksekusi validasi
```  
 

## IdentityNumber Helper Object

```php
    $objValidation = $this->validation->init($data);
    $objValidation->IdentityNumber('key')
```

 

## Ip Helper Object

```php
    $objValidation = $this->validation->init($data);
    $objValidation->Ip('key')
```      
    

 

## Number Helper Object

```php
    $objValidation = $this->validation->init($data);
    $objValidation->Number('key')
```      
    

 

## Text Helper Object

```php
    $objValidation = $this->validation->init($data);
    $objValidation->Text('key')
```    
    

 

## URL Helper Object

```php
    $objValidation = $this->validation->init($data);
    $objValidation->URL('key')
```   


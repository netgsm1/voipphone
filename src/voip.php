<?php

namespace Netgsm\Voipphone;



class voip
{   
    private $username;
    private $password;
    public function __construct()
    {
     if(isset($_ENV['NETGSM_USERCODE']))
      {
          $this->username=$_ENV['NETGSM_USERCODE'];
      }
      else{
          $this->username='x';
      }
      if(isset($_ENV['NETGSM_PASSWORD']))
      {
          $this->password=$_ENV['NETGSM_PASSWORD'];
      }
      else{
          $this->password='x';
      }
      
        
    }
    
    
    public function gorusmedetayi($data):array
    {
        if(!isset($data['date']))
        {
            $data['date']==null;
        }
        $direction=null;
        if(!isset($data["direction"]))
        {
            $direction==4;
        }
        else{
            $direction=$data['direction'];
        }
        $xmlData="<?xml version='1.0' encoding='UTF-8'?>
            <mainbody>
            <header>
                <usercode>".$this->username."</usercode>
                <password>".$this->password."</password>
                <date>".$data['date']."</date>
                <direction>".$direction."</direction>      
            </header>
            </mainbody>";
        $ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,'https://api.netgsm.com.tr/voice/report');
		$this->applyTls($ch);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
		curl_setopt($ch, CURLOPT_HTTPHEADER, Array("Content-Type: text/xml"));
		curl_setopt($ch, CURLOPT_TIMEOUT, 30);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $xmlData);
		$result = curl_exec($ch); 

        $result=array_filter(explode("<br/>",$result));
        $sonuc=array(
            30=>"Geçersiz kullanıcı adı , şifre veya kullanıcınızın API erişim izninin olmadığını gösterir.Ayrıca eğer API erişiminizde IP sınırlaması yaptıysanız ve sınırladığınız ip dışında gönderim sağlıyorsanız 30 hata kodunu alırsınız. API erişim izninizi veya IP sınırlamanızı , web arayüzümüzden; sağ üst köşede bulunan ayarlar> API işlemleri menüsunden kontrol edebilirsiniz.",
            40=>"Arama kriterlerinize göre listelenecek kayıt olmadığını ifade eder.",
            50=>"date parametresindeki tarih formatının hatalı olduğunu ifade eder. ddMMyyyyHHmm formatını kullanınız.",
            70=>"Hatalı sorgulama. Gönderdiğiniz parametrelerden birisi hatalı veya zorunlu alanlardan birinin eksik olduğunu ifade eder.",
            100=>"Sistem hatası, sınır aşımı. (dakikada en fazla 2 kez sorgulanabilir.)",
            101=>"Sistem hatası, sınır aşımı. (dakikada en fazla 2 kez sorgulanabilir.)"

        );
        if($result[0]==30 || $result[0]==40 || $result[0]==50 || $result[0]==70 || $result[0]==100 ){
           $response["code"]=$result[0];
           $response["durum"]=$sonuc[$result[0]];
        }
        else{
            $yon=array(
                1=>"Gelen Aramalar",
                2=>"Giden Aramalar",
                3=>"Cevapsız Aramalar",
                4=>"Bütün  Aramalar",

                
            );
            foreach($result as $k=>$v)
            {
                $dz[$k]=explode(" | ",$v);
                
            }

            foreach($dz as $k=>$v)
            {
                $response[$k]["number"]=$v[0];
                $response[$k]["date"]=$v[1];
                $response[$k]["sure"]=$v[2];
                $response[$k]["yoncode"]=$v[3];
                $response[$k]["yonanlam"]=$yon[$v[3]];
            }
            

        }
        return $response;
        
        
        
        


      
    }
    public function telesekretermesaj():array
    {
       
        $xmlData="<?xml version='1.0'?>
            <mainbody>
            <header>  
            <usercode>".$this->username."</usercode>
            <password>".$this->password."</password>
            </header>
            </mainbody>";

         $ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,"https://api.netgsm.com.tr/voicesms/receive");
		$this->applyTls($ch);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
		curl_setopt($ch, CURLOPT_HTTPHEADER, Array("Content-Type: text/xml"));
		curl_setopt($ch, CURLOPT_TIMEOUT, 30);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $xmlData);
		$result = curl_exec($ch);
        
        $result=array_filter(explode("<br>",$result));
        $sonuc=array(
            30=>"Geçersiz kullanıcı adı , şifre veya kullanıcınızın API erişim izninin olmadığını gösterir.Ayrıca eğer API erişiminizde IP sınırlaması yaptıysanız ve sınırladığınız ip dışında gönderim sağlıyorsanız 30 hata kodunu alırsınız. API erişim izninizi veya IP sınırlamanızı , web arayüzümüzden; sağ üst köşede bulunan ayarlar> API işlemleri menüsunden kontrol edebilirsiniz.",
            40=>"Arama kriterlerinize göre listelenecek kayıt olmadığını ifade eder.",
            50=>"Arama kriterlerindeki tarih formatının hatalı olduğunu ifade eder. (ddMMyyyyHHmm)",
            60=>"Arama kiterlerindeki startdate ve stopdate zaman farkının 30 günden fazla olduğunu ifade eder.",
            70=>"Hatalı sorgulama. Gönderdiğiniz parametrelerden birisi hatalı veya zorunlu alanlardan birinin eksik olduğunu ifade eder."

        );
        if($result[0]==30 ||$result[0]==40 || $result[0]==50 || $result[0]==60 ||$result[0]==70 )
        {
                $res["durum"]=$sonuc[$result[0]];
                $res["code"]=$result[0];
        }
        else{
            foreach($result as $k=>$v)
        {
               $dz[$k]=explode("|",$v);
            
        }

        foreach($dz as $k=>$v)
        {
            $res[$k]['number']=$v[0];
            $res[$k]['tarih']=$v[1];
            $res[$k]['dosya']=$v[3];

        }
        }
        
            
        return $res;
       
    }

    /**
     * TLS sertifika doğrulamasını açar (MITM koruması).
     * CA kaynağı sırasıyla: NETGSM_CA_PATH ortam değişkeni (dosya veya dizin),
     * sistem CA deposu, sistemde yoksa composer/ca-bundle ile gelen CA dosyası.
     */
    private function applyTls($ch): void
    {
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);

        $ca = null;
        if (!empty($_ENV['NETGSM_CA_PATH'])) {
            $ca = $_ENV['NETGSM_CA_PATH'];
        } elseif (class_exists('\Composer\CaBundle\CaBundle')) {
            $ca = \Composer\CaBundle\CaBundle::getSystemCaRootBundlePath();
        }
        if ($ca !== null) {
            curl_setopt($ch, is_dir($ca) ? CURLOPT_CAPATH : CURLOPT_CAINFO, $ca);
        }
    }
}

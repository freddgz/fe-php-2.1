<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Welcome extends CI_Controller {

	/**
	 * Index Page for this controller.
	 *
	 * Maps to the following URL
	 * 		http://example.com/index.php/welcome
	 *	- or -
	 * 		http://example.com/index.php/welcome/index
	 *	- or -
	 * Since this controller is set as the default controller in
	 * config/routes.php, it's displayed at http://example.com/
	 *
	 * So any other public methods not prefixed with an underscore will
	 * map to /index.php/welcome/<method_name>
	 * @see https://codeigniter.com/user_guide/general/urls.html
	 */
	public function index()
	{
		$method = $_SERVER['REQUEST_METHOD'];

		if($method!='POST'){
			json_output(400,array('status'=>400, 'message'=>'Bad request.'));
		}else{
			$cab = json_decode(file_get_contents('php://input'), true);
			echo "RUC ". $cab['emp_ruc'];
			echo "doc: ".$cab['doc_numero'];
			foreach ($cab['detalle'] as $item) {
				var_dump($item);
				# code...
			}
				# code...
			
        //$this->response($data);

		//$this->load->view('welcome_message');
		}
		
	}
	public function example(){
		echo 'GET/ example';
	}
	
public function email(){

     $config = Array(
              'protocol' => 'smtp',
              'smtp_host' => 'ssl://smtp.googlemail.com',
              'smtp_port' => 465,
              'smtp_user' => 'gzampuhd@gmail.com',
              'smtp_pass' => 'YTh4m3unjun9'
                );
    $this->load->library('email',$config);
    $this->email->set_newline("\r\n");

    $this->email->from("gzampuhd@gmail.com");
    $this->email->to("freddzg@gmail.com");
    $this->email->subject("Email with Codeigniter");
    $this->email->message("This is email has been sent with Codeigniter");

    if($this->email->send())
    {
        echo "Your email was sent.!";
    } else {
        show_error($this->email->print_debugger());
    }
}

	public function zipbase64($filename){
		$folder='xml_firmado/';
		$pathXmlfile=$folder.$filename.'.xml';
		$pathZipfile=$folder.$filename.'.zip';
	
		$zip = new ZipArchive;
		$zip->open($pathZipfile, ZipArchive::CREATE); 
		$localfile = basename($pathXmlfile);
		$zip->addFile($pathXmlfile,$localfile);
		$zip->close();

		echo base64_encode(file_get_contents($pathZipfile));
	}

	public function getusers()
	{		
		$this->load->model('Usuario');
		$data=$this->Usuario->getUsers();
		header('Content-Type: application/json');
		echo json_encode($data);
	}
	public function getuser($id)
	{		
		$this->load->model('Usuario');
		$data=$this->Usuario->getUser($id);
		header('Content-Type: application/json');
		echo json_encode($data);
	}
	public function hextostr(){
		$hex='BjrYY/FxKB1+hgElq/y++DvzwHw=';
	    $string='';
	    for ($i=0; $i < strlen($hex)-1; $i+=2){
	        $string .= chr(hexdec($hex[$i].$hex[$i+1]));
	    }
	    echo base64_decode($hex);
	    echo $string;
	}
	
	public function sunat(){
		

		$filename="20380456444-03-F001-666";//'20380456444-03-F002-00000026';// 
		$folder='xml_firmado/';
		$pathXmlfile=$folder.$filename.'.xml';
		$pathZipfile=$folder.$filename.'.zip';
	
		$zip = new ZipArchive;
		$zip->open($pathZipfile, ZipArchive::CREATE); 
		$localfile = basename($pathXmlfile);
		$zip->addFile($pathXmlfile,$localfile);
		$zip->close();

		//Username>20380456444MODDATOS
		//Password>moddatos
		$wsdlURL = 'https://e-beta.sunat.gob.pe/ol-ti-itcpfegem-beta/billService?wsdl';

		$XMLString = '<?xml version="1.0" encoding="UTF-8"?>
		<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:ser="http://service.sunat.gob.pe" xmlns:wsse="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd">
		 <soapenv:Header>
		     <wsse:Security>
		         <wsse:UsernameToken>
		             <wsse:Username>20380456444rparejo</wsse:Username>
		             <wsse:Password>rparejo123</wsse:Password>
		         </wsse:UsernameToken>
		     </wsse:Security>
		 </soapenv:Header>
		 <soapenv:Body>
		     <ser:sendBill>
		        <fileName>'.$filename.'.zip</fileName>
		        <contentFile>' . base64_encode(file_get_contents($pathZipfile)) . '</contentFile>
		     </ser:sendBill>
		 </soapenv:Body>
		</soapenv:Envelope>';
		//echo base64_encode(file_get_contents($pathZipfile));

		$this->load->library('Feedsoap');
		$feedsoap = new Feedsoap();
		$feedsoap->SoapClientCall($XMLString); 
		$feedsoap->__call("sendBill", array(), array());
		$result = $feedsoap->__getLastResponse();
		//Descargamos el Archivo Response
		$archivo = fopen($folder.'C'.$filename.'.xml','w+');
		fputs($archivo,$result);		
		fclose($archivo);

		//LEEMOS EL ARCHIVO XML
		$xml = simplexml_load_file($folder.'/C'.$filename.'.xml'); 
		foreach ($xml->xpath('//applicationResponse') as $response){ }
		//AQUI DESCARGAMOS EL ARCHIVO CDR(CONSTANCIA DE RECEPCIÓN)
		$cdr=base64_decode($response);
		$archivo = fopen($folder.'R-'.$filename.'.zip','w+');
		fputs($archivo,$cdr);
		fclose($archivo);

		//DESCOMPRIMIR ARCHIVO
		$zip = new ZipArchive;
		$res = $zip->open($folder.'R-'.$filename.'.zip');
		if ($res === TRUE) {
		$zip->extractTo($folder);
		$zip->close();
			echo 'ok';
		} else {
			echo 'failed';
		}
		//Eliminamos el Archivo Response
		unlink($folder.'C'.$filename.'.xml');
	}
	public function firmar(){
		$filename='20380456444-03-F001-666';// '20380456444-03-F002-00000026';//

		$this->load->library('XMLSecurityDSig');
		$this->load->library('XMLSecurityKey');
		$doc = new DOMDocument();
		$doc->load('xml/'.$filename.'.xml');
		//$doc->xmlStandalone = false;
		//$doc->formatOutput = true;
		//$doc->preserveWhiteSpace = false;
		
		// Crear un nuevo objeto de seguridad
		$objDSig = new XMLSecurityDSig();
		// Utilizar la canonización exclusiva de c14n
		$objDSig->setCanonicalMethod(XMLSecurityDSig::EXC_C14N);
		// Firmar con SHA-256
		$objDSig->addReference(
		    $doc,
		    XMLSecurityDSig::SHA1,
		    array('http://www.w3.org/2000/09/xmldsig#enveloped-signature'),
		    array('force_uri' => true)
		);
		//Crear una nueva clave de seguridad (privada)
		$objKey = new XMLSecurityKey;
		$objKey->init(XMLSecurityKey::RSA_SHA1, array('type' => 'private'));
		//Cargamos la clave privada
		
		$objKey->loadKey('certificado.key', true);
		$objDSig->sign($objKey);
		// Agregue la clave pública asociada a la firma
		$objDSig->add509Cert(file_get_contents('certificado.cer'), true, false, array('subjectName' => true)); // array('issuerSerial' => true, 'subjectName' => true));
		// Anexar la firma al XML
		$objDSig->appendSignature($doc->getElementsByTagName('ExtensionContent')->item(1));
		
		//$doc->formatOutput = true;

		// Guardar el XML firmado
		$doc->save('xml_firmado/'.$filename.'.xml');
	}
	public function factura(){
		$ruc ='20380456444';
		$file =$ruc.'-03-F001-666.xml';

		$dom = new DomDocument("1.0","ISO-8859-1");
		$dom->xmlStandalone = false;
		//$dom->formatOutput = true;
		$dom->preserveWhiteSpace = false;

		$Invoice = $dom->createElement('Invoice');
		$dom->appendChild($Invoice);
		
		$Invoice->setAttribute('xmlns','urn:oasis:names:specification:ubl:schema:xsd:Invoice-2');
		$Invoice->setAttribute('xmlns:cac','urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2');
		$Invoice->setAttribute('xmlns:cbc','urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2');
		$Invoice->setAttribute('xmlns:ccts','urn:un:unece:uncefact:documentation:2');
		$Invoice->setAttribute('xmlns:ds','http://www.w3.org/2000/09/xmldsig#');
		$Invoice->setAttribute('xmlns:ext','urn:oasis:names:specification:ubl:schema:xsd:CommonExtensionComponents-2');
		$Invoice->setAttribute('xmlns:qdt','urn:oasis:names:specification:ubl:schema:xsd:QualifiedDatatypes-2');
		$Invoice->setAttribute('xmlns:sac','urn:sunat:names:specification:ubl:peru:schema:xsd:SunatAggregateComponents-1');
		$Invoice->setAttribute('xmlns:udt','urn:un:unece:uncefact:data:specification:UnqualifiedDataTypesSchemaModule:2');
		$Invoice->setAttribute('xmlns:xsi','http://www.w3.org/2001/XMLSchema-instance');

		$UBLExtensions = $dom->createElement('ext:UBLExtensions');
		$Invoice->appendChild($UBLExtensions);

		$UBLExtension1 = $dom->createElement('ext:UBLExtension');
		$UBLExtensions->appendChild($UBLExtension1);
		$ExtensionContent1 = $dom->createElement('ext:ExtensionContent');
		$UBLExtension1->appendChild($ExtensionContent1);

		$AdditionalInformation = $dom->createElement('sac:AdditionalInformation');
		$ExtensionContent1->appendChild($AdditionalInformation);

		//agrupa1 getDocu_gravada
		$AdditionalMonetaryTotal1 = $dom->createElement('sac:AdditionalMonetaryTotal');
		$AdditionalInformation->appendChild($AdditionalMonetaryTotal1);
		$AdditionalMonetaryTotal1->appendChild($dom->createElement('cbc:ID',"1001"));
		$PayableAmount1 = $dom->createElement('cbc:PayableAmount',"100.00");
		$PayableAmount1->setAttribute('currencyID','PEN');
		$AdditionalMonetaryTotal1->appendChild($PayableAmount1);
		$anticipoCero1001="1";
		//agrupa2 getDocu_inafecta
		$AdditionalMonetaryTotal2 = $dom->createElement('sac:AdditionalMonetaryTotal');
		$AdditionalInformation->appendChild($AdditionalMonetaryTotal2);
		$AdditionalMonetaryTotal2->appendChild($dom->createElement('cbc:ID',"1002"));
		$PayableAmount2 = $dom->createElement('cbc:PayableAmount',"100.00");
		$PayableAmount2->setAttribute('currencyID','PEN');
		$AdditionalMonetaryTotal2->appendChild($PayableAmount2);
		$anticipoCero1002="1";
		//agrupa3 getDocu_exonerada
		$AdditionalMonetaryTotal3 = $dom->createElement('sac:AdditionalMonetaryTotal');
		$AdditionalInformation->appendChild($AdditionalMonetaryTotal3);
		$AdditionalMonetaryTotal3->appendChild($dom->createElement('cbc:ID',"1003"));
		$PayableAmount3 = $dom->createElement('cbc:PayableAmount',"100.00");
		$PayableAmount3->setAttribute('currencyID','PEN');
		$AdditionalMonetaryTotal3->appendChild($PayableAmount3);
		$anticipoCero1003="1";
		//agrupa4 getDocu_gratuita
		$AdditionalMonetaryTotal4 = $dom->createElement('sac:AdditionalMonetaryTotal');
		$AdditionalInformation->appendChild($AdditionalMonetaryTotal4);
		$AdditionalMonetaryTotal4->appendChild($dom->createElement('cbc:ID',"1004"));
		$PayableAmount4 = $dom->createElement('cbc:PayableAmount',"100.00");
		$PayableAmount4->setAttribute('currencyID','PEN');
		$AdditionalMonetaryTotal4->appendChild($PayableAmount4);
		//agrupa5 getDocu_descuento
		$AdditionalMonetaryTotal5 = $dom->createElement('sac:AdditionalMonetaryTotal');
		$AdditionalInformation->appendChild($AdditionalMonetaryTotal5);
		$AdditionalMonetaryTotal5->appendChild($dom->createElement('cbc:ID',"2005"));
		$PayableAmount5 = $dom->createElement('cbc:PayableAmount',"100.00");
		$PayableAmount5->setAttribute('currencyID','PEN');
		$AdditionalMonetaryTotal5->appendChild($PayableAmount5);
		for ($i=1; $i < 2; $i++) { 
			$AdditionalProperty = $dom->createElement('sac:AdditionalProperty');
			$AdditionalInformation->appendChild($AdditionalProperty);
			$AdditionalProperty->appendChild($dom->createElement('cbc:ID','1000'));
			$Value=$dom->createElement('cbc:Value','CIEN Y 00/100');
			$AdditionalProperty->appendChild($Value);
			//$Value->appendChild($dom->createCDATASection("CIEN Y 00/100"));
		}

		//signature 
		$UBLExtension2 = $dom->createElement('ext:UBLExtension');
		$UBLExtensions->appendChild($UBLExtension2);
		$ExtensionContent2 = $dom->createElement('ext:ExtensionContent',' ');
		$UBLExtension2->appendChild($ExtensionContent2);		


		//bloque 1
		$Invoice->appendChild($dom->createElement('cbc:UBLVersionID','2.0'));
		$Invoice->appendChild($dom->createElement('cbc:CustomizationID','1.0'));
		$Invoice->appendChild($dom->createElement('cbc:ID','F001-666'));
		$Invoice->appendChild($dom->createElement('cbc:IssueDate','2016-03-14'));
		$Invoice->appendChild($dom->createElement('cbc:InvoiceTypeCode','03'));
		$Invoice->appendChild($dom->createElement('cbc:DocumentCurrencyCode','PEN'));

		//bloque2 cac:Signature
		$Signature = $dom->createElement('cac:Signature');
		$Invoice->appendChild($Signature);
		$Signature->appendChild($dom->createElement('cbc:ID',$ruc));
		$SignatoryParty = $dom->createElement('cac:SignatoryParty');
		$Signature->appendChild($SignatoryParty);
		$PartyIdentification = $dom->createElement('cac:PartyIdentification');
		$SignatoryParty->appendChild($PartyIdentification);
		$PartyIdentification->appendChild($dom->createElement('cbc:ID',$ruc));
		$PartyName = $dom->createElement('cac:PartyName');
		$SignatoryParty->appendChild($PartyName);
		$Name = $dom->createElement('cbc:Name','NOMBRE');
		$PartyName->appendChild($Name);
		//$Name->appendChild($dom->createCDATASection("NOMBRE"));


		$DigitalSignatureAttachment = $dom->createElement('cac:DigitalSignatureAttachment');
		$Signature->appendChild($DigitalSignatureAttachment);
		$ExternalReference = $dom->createElement('cac:ExternalReference');
		$DigitalSignatureAttachment->appendChild($ExternalReference);
		$ExternalReference->appendChild($dom->createElement('cbc:URI',$ruc));

		//bloque3 cac:AccountingSupplierParty
		$AccountingSupplierParty = $dom->createElement('cac:AccountingSupplierParty');
		$Invoice->appendChild($AccountingSupplierParty);
		$AccountingSupplierParty->appendChild($dom->createElement('cbc:CustomerAssignedAccountID',$ruc));
		$AccountingSupplierParty->appendChild($dom->createElement('cbc:AdditionalAccountID','6'));
		$Party = $dom->createElement('cac:Party');
		$AccountingSupplierParty->appendChild($Party);
		$PartyName = $dom->createElement('cac:PartyName');
		$Party->appendChild($PartyName);
		$Name = $dom->createElement('cbc:Name','NOMBRE');
		$PartyName->appendChild($Name);
		//$Name->appendChild($dom->createCDATASection("NOMBRE"));


		$PostalAddress = $dom->createElement('cac:PostalAddress');
		$Party->appendChild($PostalAddress);
		$PostalAddress->appendChild($dom->createElement('cbc:ID','150111'));
		$PostalAddress->appendChild($dom->createElement('cbc:StreetName','AV. LOS PRECURSORES'));
		$PostalAddress->appendChild($dom->createElement('cbc:CitySubdivisionName','URB. MIGUEL GRAU'));
		$PostalAddress->appendChild($dom->createElement('cbc:CityName','LIMA'));
		$PostalAddress->appendChild($dom->createElement('cbc:CountrySubentity','LIMA'));
		$PostalAddress->appendChild($dom->createElement('cbc:District','EL AGUSTINO'));
		$Country = $dom->createElement('cac:Country');
		$PostalAddress->appendChild($Country);
		$Country->appendChild($dom->createElement('cbc:IdentificationCode','PE'));
		$PartyLegalEntity = $dom->createElement('cac:PartyLegalEntity');
		$Party->appendChild($PartyLegalEntity);
		$RegistrationName = $dom->createElement('cbc:RegistrationName','NOMBRE');
		$PartyLegalEntity->appendChild($RegistrationName);
		//$RegistrationName->appendChild($dom->createCDATASection("NOMBRE"));
		//bloque 4
		$AccountingCustomerParty = $dom->createElement('cac:AccountingCustomerParty');
		$Invoice->appendChild($AccountingCustomerParty);
		$AccountingCustomerParty->appendChild($dom->createElement('cbc:CustomerAssignedAccountID','12345678'));
		$AccountingCustomerParty->appendChild($dom->createElement('cbc:AdditionalAccountID','1'));
		$Party2 = $dom->createElement('cac:Party');
		$AccountingCustomerParty->appendChild($Party2);
		$PartyLegalEntity2 = $dom->createElement('cac:PartyLegalEntity');
		$Party2->appendChild($PartyLegalEntity2);
		$PartyLegalEntity2->appendChild($dom->createElement('cbc:RegistrationName','NOMBRE'));

		//bloque 5 getDocu_igv
		$TaxTotal = $dom->createElement('cac:TaxTotal');
		$Invoice->appendChild($TaxTotal);
		$TaxAmount = $dom->createElement('cbc:TaxAmount','100.00');
		$TaxTotal->appendChild($TaxAmount);
		$TaxAmount->setAttribute('currencyID','PEN');
		$TaxSubtotal = $dom->createElement('cac:TaxSubtotal');
		$TaxTotal->appendChild($TaxSubtotal);
		$TaxAmount2 = $dom->createElement('cbc:TaxAmount','100.00');
		$TaxSubtotal->appendChild($TaxAmount2);
		$TaxAmount2->setAttribute('currencyID','PEN');
		$TaxCategory = $dom->createElement('cac:TaxCategory');
		$TaxSubtotal->appendChild($TaxCategory);
		$TaxScheme = $dom->createElement('cac:TaxScheme');
		$TaxCategory->appendChild($TaxScheme);
		$TaxScheme->appendChild($dom->createElement('cbc:ID','1000'));
		$TaxScheme->appendChild($dom->createElement('cbc:Name','IGV'));
		$TaxScheme->appendChild($dom->createElement('cbc:TaxTypeCode','VAT'));

		//bloque 6
		$LegalMonetaryTotal = $dom->createElement('cac:LegalMonetaryTotal');
		$Invoice->appendChild($LegalMonetaryTotal);
		// if getDocu_descuento
		/*
		$AllowanceTotalAmount = $dom->createElement('cbc:AllowanceTotalAmount','0.00');
		$LegalMonetaryTotal->appendChild($AllowanceTotalAmount);
		$AllowanceTotalAmount->setAttribute('currencyID','PEN');
		*/
		
		$PayableAmount = $dom->createElement('cbc:PayableAmount','100.00');
		$LegalMonetaryTotal->appendChild($PayableAmount);
		$PayableAmount->setAttribute('currencyID','PEN');

		//detalle factura
		for ($i=1; $i < 2; $i++) { 
			$InvoiceLine = $dom->createElement('cac:InvoiceLine');
			$Invoice->appendChild($InvoiceLine);
			$InvoiceLine->appendChild($dom->createElement('cbc:ID',$i));
			$InvoicedQuantity = $dom->createElement('cbc:InvoicedQuantity','20.00');
			$InvoiceLine->appendChild($InvoicedQuantity);
			$InvoicedQuantity->setAttribute('unitCode','NIU');

			$LineExtensionAmount = $dom->createElement('cbc:LineExtensionAmount','100.00');
			$InvoiceLine->appendChild($LineExtensionAmount);
			$LineExtensionAmount->setAttribute('currencyID','PEN');

			$PricingReference = $dom->createElement('cac:PricingReference');
			$InvoiceLine->appendChild($PricingReference);
			$AlternativeConditionPrice = $dom->createElement('cac:AlternativeConditionPrice');
			$PricingReference->appendChild($AlternativeConditionPrice);
			$PriceAmount = $dom->createElement('cbc:PriceAmount','100.00');
			$AlternativeConditionPrice->appendChild($PriceAmount);
			$PriceAmount->setAttribute('currencyID','PEN');
			$AlternativeConditionPrice->appendChild($dom->createElement('cbc:PriceTypeCode','01'));

			$TaxTotal = $dom->createElement('cac:TaxTotal');
			$InvoiceLine->appendChild($TaxTotal);
			$TaxAmount = $dom->createElement('cbc:TaxAmount','100.00');
			$TaxTotal->appendChild($TaxAmount);
			$TaxAmount->setAttribute('currencyID','PEN');
			$TaxSubtotal = $dom->createElement('cac:TaxSubtotal');
			$TaxTotal->appendChild($TaxSubtotal);
			$TaxableAmount = $dom->createElement('cbc:TaxableAmount','100.00');
			$TaxSubtotal->appendChild($TaxableAmount);
			$TaxableAmount->setAttribute('currencyID','PEN');
			$TaxAmount2 = $dom->createElement('cbc:TaxAmount','100.00');
			$TaxSubtotal->appendChild($TaxAmount2);
			$TaxAmount2->setAttribute('currencyID','PEN');
			$TaxSubtotal->appendChild($dom->createElement('cbc:Percent','0.0'));

			$TaxCategory = $dom->createElement('cac:TaxCategory');
			$TaxSubtotal->appendChild($TaxCategory);
			$TaxCategory->appendChild($dom->createElement('cbc:ID','VAT'));
			$TaxCategory->appendChild($dom->createElement('cbc:TaxExemptionReasonCode','10'));
			$TaxCategory->appendChild($dom->createElement('cbc:TierRange','10'));
			$TaxScheme = $dom->createElement('cac:TaxScheme');
			$TaxCategory->appendChild($TaxScheme);
			$TaxScheme->appendChild($dom->createElement('cbc:ID','1000'));
			$TaxScheme->appendChild($dom->createElement('cbc:Name','IGV'));
			$TaxScheme->appendChild($dom->createElement('cbc:TaxTypeCode','VAT'));

			$Item = $dom->createElement('cac:Item');
			$InvoiceLine->appendChild($Item);
			$Item->appendChild($dom->createElement('cbc:Description','DESCRIPCION'));
			$SellersItemIdentification = $dom->createElement('cac:SellersItemIdentification');
			$Item->appendChild($SellersItemIdentification);
			$SellersItemIdentification->appendChild($dom->createElement('cbc:ID','GLG199'));

			$Price = $dom->createElement('cac:Price');
			$InvoiceLine->appendChild($Price);
			$PriceAmount = $dom->createElement('cbc:PriceAmount','100.00');
			$Price->appendChild($PriceAmount);
			$PriceAmount->setAttribute('currencyID','PEN');
		}

		
		$dom->formatOutput = true;
		
		$dom->save( 'xml/'.$file);
	}

	public function generarAnulacion(){
		$ruc ='20566229774';

		$name = 'RA-20181002-001';
		

		$dom = new DomDocument("1.0","ISO-8859-1");
		$dom->xmlStandalone = false;
		//$dom->formatOutput = true;
		$dom->preserveWhiteSpace = false;

		$Invoice = $dom->createElement('VoidedDocuments');
		$dom->appendChild($Invoice);
		
		$Invoice->setAttribute('xmlns','urn:sunat:names:specification:ubl:peru:schema:xsd:VoidedDocuments-1');
		$Invoice->setAttribute('xmlns:cac','urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2');
		$Invoice->setAttribute('xmlns:cbc','urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2');
		$Invoice->setAttribute('xmlns:ds','http://www.w3.org/2000/09/xmldsig#');
		$Invoice->setAttribute('xmlns:ext','urn:oasis:names:specification:ubl:schema:xsd:CommonExtensionComponents-2');
		$Invoice->setAttribute('xmlns:qdt','urn:oasis:names:specification:ubl:schema:xsd:QualifiedDatatypes-2');
		$Invoice->setAttribute('xmlns:sac','urn:sunat:names:specification:ubl:peru:schema:xsd:SunatAggregateComponents-1');
		$Invoice->setAttribute('xmlns:xsi','http://www.w3.org/2001/XMLSchema-instance');

		$UBLExtensions = $dom->createElement('ext:UBLExtensions');
		$Invoice->appendChild($UBLExtensions);

		//signature 
		$UBLExtension1 = $dom->createElement('ext:UBLExtension');
		$UBLExtensions->appendChild($UBLExtension1);
		$ExtensionContent1 = $dom->createElement('ext:ExtensionContent');
		$UBLExtension1->appendChild($ExtensionContent1);

		//bloque 1
		$Invoice->appendChild($dom->createElement('cbc:UBLVersionID','2.0'));
		$Invoice->appendChild($dom->createElement('cbc:CustomizationID','1.0'));
		$Invoice->appendChild($dom->createElement('cbc:ID',$name));
		$Invoice->appendChild($dom->createElement('cbc:ReferenceDate','2018-10-01'));
		$Invoice->appendChild($dom->createElement('cbc:IssueDate','2018-10-02'));
		//$Invoice->appendChild($dom->createElement('cbc:InvoiceTypeCode','03'));
		//$Invoice->appendChild($dom->createElement('cbc:DocumentCurrencyCode','PEN'));

		//bloque2 cac:Signature
		$Signature = $dom->createElement('cac:Signature');
		$Invoice->appendChild($Signature);
		$Signature->appendChild($dom->createElement('cbc:ID',$name));
		$SignatoryParty = $dom->createElement('cac:SignatoryParty');
		$Signature->appendChild($SignatoryParty);
		$PartyIdentification = $dom->createElement('cac:PartyIdentification');
		$SignatoryParty->appendChild($PartyIdentification);
		$PartyIdentification->appendChild($dom->createElement('cbc:ID',$ruc));
		$PartyName = $dom->createElement('cac:PartyName');
		$SignatoryParty->appendChild($PartyName);
		$Name = $dom->createElement('cbc:Name','NOMBRE');
		$PartyName->appendChild($Name);
		//$Name->appendChild($dom->createCDATASection("NOMBRE"));


		$DigitalSignatureAttachment = $dom->createElement('cac:DigitalSignatureAttachment');
		$Signature->appendChild($DigitalSignatureAttachment);
		$ExternalReference = $dom->createElement('cac:ExternalReference');
		$DigitalSignatureAttachment->appendChild($ExternalReference);
		$ExternalReference->appendChild($dom->createElement('cbc:URI',$ruc.'-'.$name));

		//bloque3 cac:AccountingSupplierParty
		$AccountingSupplierParty = $dom->createElement('cac:AccountingSupplierParty');
		$Invoice->appendChild($AccountingSupplierParty);
		$AccountingSupplierParty->appendChild($dom->createElement('cbc:CustomerAssignedAccountID',$ruc));
		$AccountingSupplierParty->appendChild($dom->createElement('cbc:AdditionalAccountID','6'));
		$Party = $dom->createElement('cac:Party');
		$AccountingSupplierParty->appendChild($Party);
		$PartyName = $dom->createElement('cac:PartyLegalEntity');
		$Party->appendChild($PartyName);
		$Name = $dom->createElement('cbc:RegistrationName','NOMBRE');
		$PartyName->appendChild($Name);

		for ($i=1; $i < 3; $i++) { 
			$InvoiceLine = $dom->createElement('sac:VoidedDocumentsLine');
			$Invoice->appendChild($InvoiceLine);
			$InvoiceLine->appendChild($dom->createElement('cbc:LineID',$i));
			$InvoiceLine->appendChild($dom->createElement('cbc:DocumentTypeCode','01'));
			$InvoiceLine->appendChild($dom->createElement('sac:DocumentSerialID','F001'));
			$InvoiceLine->appendChild($dom->createElement('sac:DocumentNumberID',$i));
			$InvoiceLine->appendChild($dom->createElement('sac:VoidReasonDescription','CANCELACION'));//ERROR EN SISTEMA|CANCELACION
		}
		
		$dom->formatOutput = true;
		$dom->save( 'xml/'.$ruc.'-'.$name.'.xml');
		
		//*************************************FIRMAR
			$filename =$ruc.'-'.$name;

			$this->load->library('XMLSecurityDSig');
		$this->load->library('XMLSecurityKey');
		$doc = new DOMDocument();
		$doc->load('xml/'.$filename.'.xml');
		//$doc->xmlStandalone = false;
		//$doc->formatOutput = true;
		//$doc->preserveWhiteSpace = false;
		
		// Crear un nuevo objeto de seguridad
		$objDSig = new XMLSecurityDSig();
		// Utilizar la canonización exclusiva de c14n
		$objDSig->setCanonicalMethod(XMLSecurityDSig::EXC_C14N);
		// Firmar con SHA-256
		$objDSig->addReference(
		    $doc,
		    XMLSecurityDSig::SHA1,
		    array('http://www.w3.org/2000/09/xmldsig#enveloped-signature'),
		    array('force_uri' => true)
		);
		//Crear una nueva clave de seguridad (privada)
		$objKey = new XMLSecurityKey;
		$objKey->init(XMLSecurityKey::RSA_SHA1, array('type' => 'private'));
		//Cargamos la clave privada
		
		$objKey->loadKey('dubau.key', true);
		$objDSig->sign($objKey);
		// Agregue la clave pública asociada a la firma
		$objDSig->add509Cert(file_get_contents('dubau.cer'), true, false, array('subjectName' => true)); // array('issuerSerial' => true, 'subjectName' => true));
		// Anexar la firma al XML
		$objDSig->appendSignature($doc->getElementsByTagName('ExtensionContent')->item(0));
		
		//$doc->formatOutput = true;

		// Guardar el XML firmado
		$doc->save('xml_firmado/'.$filename.'.xml');


		//var_dump(expression)
		//$Hash=$doc->getElementsByTagName('DigestValue')->item(0)->nodeValue;

		//******************************ENVIAR A SUNAT
		//$filename="20380456444-03-F001-666";//'20380456444-03-F002-00000026';// 


		$folder='xml_firmado/';
		$pathXmlfile=$folder.$filename.'.xml';
		$pathZipfile=$folder.$filename.'.zip';
	
		$zip = new ZipArchive;
		$zip->open($pathZipfile, ZipArchive::CREATE); 
		$localfile = basename($pathXmlfile);
		$zip->addFile($pathXmlfile,$localfile);
		$zip->close();

		//Username>20380456444MODDATOS
		//Password>moddatos
		$wsdlURL = 'https://e-beta.sunat.gob.pe/ol-ti-itcpfegem-beta/billService?wsdl';

		$XMLString = '<?xml version="1.0" encoding="UTF-8"?>
		<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:ser="http://service.sunat.gob.pe" xmlns:wsse="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd">
		 <soapenv:Header>
		     <wsse:Security>
		         <wsse:UsernameToken>
		             <wsse:Username>20566229774LVENEEDO</wsse:Username>
		             <wsse:Password>oialimull</wsse:Password>
		         </wsse:UsernameToken>
		     </wsse:Security>
		 </soapenv:Header>
		 <soapenv:Body>
		     <ser:sendSummary>
		        <fileName>'.$filename.'.zip</fileName>
		        <contentFile>' . base64_encode(file_get_contents($pathZipfile)) . '</contentFile>
		     </ser:sendSummary>
		 </soapenv:Body>
		</soapenv:Envelope>';
		//echo base64_encode(file_get_contents($pathZipfile));

		$this->load->library('Feedsoap');
		$feedsoap = new Feedsoap();
		$feedsoap->SoapClientCall($XMLString); 
		$feedsoap->__call("sendSummary", array(), array());
		$result = $feedsoap->__getLastResponse();
		
		var_dump($result);
	}

	public function generarResumen(){
		$ruc ='20566229774';

		$name = 'RC-20181002-001';
		

		$dom = new DomDocument("1.0","ISO-8859-1");
		$dom->xmlStandalone = false;
		//$dom->formatOutput = true;
		$dom->preserveWhiteSpace = false;

		$Invoice = $dom->createElement('SummaryDocuments');
		$dom->appendChild($Invoice);
		
		$Invoice->setAttribute('xmlns','urn:sunat:names:specification:ubl:peru:schema:xsd:SummaryDocuments-1');
		$Invoice->setAttribute('xmlns:cac','urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2');
		$Invoice->setAttribute('xmlns:cbc','urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2');
		$Invoice->setAttribute('xmlns:ds','http://www.w3.org/2000/09/xmldsig#');
		$Invoice->setAttribute('xmlns:ext','urn:oasis:names:specification:ubl:schema:xsd:CommonExtensionComponents-2');
		$Invoice->setAttribute('xmlns:sac','urn:sunat:names:specification:ubl:peru:schema:xsd:SunatAggregateComponents-1');
		$Invoice->setAttribute('xmlns:xsi','http://www.w3.org/2001/XMLSchema-instance');

		$UBLExtensions = $dom->createElement('ext:UBLExtensions');
		$Invoice->appendChild($UBLExtensions);

		//signature 
		$UBLExtension1 = $dom->createElement('ext:UBLExtension');
		$UBLExtensions->appendChild($UBLExtension1);
		$ExtensionContent1 = $dom->createElement('ext:ExtensionContent');
		$UBLExtension1->appendChild($ExtensionContent1);

		//bloque 1
		$Invoice->appendChild($dom->createElement('cbc:UBLVersionID','2.0'));
		$Invoice->appendChild($dom->createElement('cbc:CustomizationID','1.1'));
		$Invoice->appendChild($dom->createElement('cbc:ID',$name));
		$Invoice->appendChild($dom->createElement('cbc:ReferenceDate','2018-10-01'));
		$Invoice->appendChild($dom->createElement('cbc:IssueDate','2018-10-02'));

		//bloque2 cac:Signature
		$Signature = $dom->createElement('cac:Signature');
		$Invoice->appendChild($Signature);
		$Signature->appendChild($dom->createElement('cbc:ID',$name));
		$SignatoryParty = $dom->createElement('cac:SignatoryParty');
		$Signature->appendChild($SignatoryParty);
		$PartyIdentification = $dom->createElement('cac:PartyIdentification');
		$SignatoryParty->appendChild($PartyIdentification);
		$PartyIdentification->appendChild($dom->createElement('cbc:ID',$ruc));
		$PartyName = $dom->createElement('cac:PartyName');
		$SignatoryParty->appendChild($PartyName);
		$Name = $dom->createElement('cbc:Name','NOMBRE');
		$PartyName->appendChild($Name);
		//$Name->appendChild($dom->createCDATASection("NOMBRE"));
		$DigitalSignatureAttachment = $dom->createElement('cac:DigitalSignatureAttachment');
		$Signature->appendChild($DigitalSignatureAttachment);
		$ExternalReference = $dom->createElement('cac:ExternalReference');
		$DigitalSignatureAttachment->appendChild($ExternalReference);
		$ExternalReference->appendChild($dom->createElement('cbc:URI',$ruc.'-'.$name));

		//bloque3 cac:AccountingSupplierParty
		$AccountingSupplierParty = $dom->createElement('cac:AccountingSupplierParty');
		$Invoice->appendChild($AccountingSupplierParty);
		$AccountingSupplierParty->appendChild($dom->createElement('cbc:CustomerAssignedAccountID',$ruc));
		$AccountingSupplierParty->appendChild($dom->createElement('cbc:AdditionalAccountID','6'));
		$Party = $dom->createElement('cac:Party');
		$AccountingSupplierParty->appendChild($Party);
		$PartyName = $dom->createElement('cac:PartyLegalEntity');
		$Party->appendChild($PartyName);
		$Name = $dom->createElement('cbc:RegistrationName','NOMBRE');
		$PartyName->appendChild($Name);

		for ($i=1; $i < 2; $i++) { 
			$InvoiceLine = $dom->createElement('sac:SummaryDocumentsLine');
			$Invoice->appendChild($InvoiceLine);
			$InvoiceLine->appendChild($dom->createElement('cbc:LineID',$i));
			$InvoiceLine->appendChild($dom->createElement('cbc:DocumentTypeCode','03'));
			$InvoiceLine->appendChild($dom->createElement('cbc:ID','BB14-33386'));
			$AccountingCustomerParty = $dom->createElement('cac:AccountingCustomerParty');
				$AccountingCustomerParty->appendChild($dom->createElement('cbc:CustomerAssignedAccountID','41614074'));
				$AccountingCustomerParty->appendChild($dom->createElement('cbc:AdditionalAccountID','1'));
				$InvoiceLine->appendChild($AccountingCustomerParty);
			$Status = $dom->createElement('cac:Status');
				$Status->appendChild($dom->createElement('cbc:ConditionCode','1'));
				$InvoiceLine->appendChild($Status);
			$TotalAmount = $dom->createElement('sac:TotalAmount', '190.90');
                $TotalAmount->setAttribute('currencyID', 'PEN');
                $InvoiceLine->appendChild($TotalAmount);
            $BillingPayment = $dom->createElement('sac:BillingPayment');
            	$PaidAmount = $dom->createElement('cbc:PaidAmount', '161.78');
	                $PaidAmount->setAttribute('currencyID', 'PEN');
	                $BillingPayment->appendChild($PaidAmount);
	            $BillingPayment->appendChild($dom->createElement('cbc:InstructionID','01'));

            $TaxTotal = $dom->createElement('cac:TaxTotal');
            $InvoiceLine->appendChild($TaxTotal);
            $TaxAmount = $dom->createElement('cbc:TaxAmount', '29.12');
            $TaxTotal->appendChild($TaxAmount);
            $TaxAmount->setAttribute('currencyID', 'PEN');
            $TaxSubtotal = $dom->createElement('cac:TaxSubtotal');
            $TaxTotal->appendChild($TaxSubtotal);
            $TaxAmount2 = $dom->createElement('cbc:TaxAmount', '29.12');
            $TaxSubtotal->appendChild($TaxAmount2);
            $TaxAmount2->setAttribute('currencyID', 'PEN');

            $TaxCategory = $dom->createElement('cac:TaxCategory');
            $TaxSubtotal->appendChild($TaxCategory);
            $TaxScheme = $dom->createElement('cac:TaxScheme');
            $TaxCategory->appendChild($TaxScheme);
            $TaxScheme->appendChild($dom->createElement('cbc:ID', '1000'));
            $TaxScheme->appendChild($dom->createElement('cbc:Name', 'IGV'));
            $TaxScheme->appendChild($dom->createElement('cbc:TaxTypeCode', 'VAT'));

		}
		
		$dom->formatOutput = true;
		$dom->save( 'xml/'.$ruc.'-'.$name.'.xml');

		//header('Content-type: text/xml');
		//echo $dom->saveXML();


		//*************************************FIRMAR
			$filename =$ruc.'-'.$name;

			$this->load->library('XMLSecurityDSig');
		$this->load->library('XMLSecurityKey');
		$doc = new DOMDocument();
		$doc->load('xml/'.$filename.'.xml');
		//$doc->xmlStandalone = false;
		//$doc->formatOutput = true;
		//$doc->preserveWhiteSpace = false;
		
		// Crear un nuevo objeto de seguridad
		$objDSig = new XMLSecurityDSig();
		// Utilizar la canonización exclusiva de c14n
		$objDSig->setCanonicalMethod(XMLSecurityDSig::EXC_C14N);
		// Firmar con SHA-256
		$objDSig->addReference(
		    $doc,
		    XMLSecurityDSig::SHA1,
		    array('http://www.w3.org/2000/09/xmldsig#enveloped-signature'),
		    array('force_uri' => true)
		);
		//Crear una nueva clave de seguridad (privada)
		$objKey = new XMLSecurityKey;
		$objKey->init(XMLSecurityKey::RSA_SHA1, array('type' => 'private'));
		//Cargamos la clave privada
		
		$objKey->loadKey('dubau.key', true);
		$objDSig->sign($objKey);
		// Agregue la clave pública asociada a la firma
		$objDSig->add509Cert(file_get_contents('dubau.cer'), true, false, array('subjectName' => true)); // array('issuerSerial' => true, 'subjectName' => true));
		// Anexar la firma al XML
		$objDSig->appendSignature($doc->getElementsByTagName('ExtensionContent')->item(0));
		
		//$doc->formatOutput = true;

		// Guardar el XML firmado
		$doc->save('xml_firmado/'.$filename.'.xml');

 		//******************************ENVIAR A SUNAT
		//$filename="20380456444-03-F001-666";//'20380456444-03-F002-00000026';// 


		$folder='xml_firmado/';
		$pathXmlfile=$folder.$filename.'.xml';
		$pathZipfile=$folder.$filename.'.zip';
	
		$zip = new ZipArchive;
		$zip->open($pathZipfile, ZipArchive::CREATE); 
		$localfile = basename($pathXmlfile);
		$zip->addFile($pathXmlfile,$localfile);
		$zip->close();

		//Username>20380456444MODDATOS
		//Password>moddatos
		$wsdlURL = 'https://e-beta.sunat.gob.pe/ol-ti-itcpfegem-beta/billService?wsdl';

		$XMLString = '<?xml version="1.0" encoding="UTF-8"?>
		<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:ser="http://service.sunat.gob.pe" xmlns:wsse="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd">
		 <soapenv:Header>
		     <wsse:Security>
		         <wsse:UsernameToken>
		             <wsse:Username>20566229774LVENEEDO</wsse:Username>
		             <wsse:Password>oialimull</wsse:Password>
		         </wsse:UsernameToken>
		     </wsse:Security>
		 </soapenv:Header>
		 <soapenv:Body>
		     <ser:sendSummary>
		        <fileName>'.$filename.'.zip</fileName>
		        <contentFile>' . base64_encode(file_get_contents($pathZipfile)) . '</contentFile>
		     </ser:sendSummary>
		 </soapenv:Body>
		</soapenv:Envelope>';
		//echo base64_encode(file_get_contents($pathZipfile));

		$this->load->library('Feedsoap');
		$feedsoap = new Feedsoap();
		$feedsoap->SoapClientCall($XMLString); 
		$feedsoap->__call("sendSummary", array(), array());
		$result = $feedsoap->__getLastResponse();
		
		var_dump($result);
		
}

}
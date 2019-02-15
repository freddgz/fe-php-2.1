<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Factura extends CI_Controller {

    protected $username="2056622977400DL1RGZ";
    protected $password="12345678";
    protected $url_consult="https://www.sunat.gob.pe/ol-it-wsconscpegem/billConsultService?wsdl";
    protected $url_beta="https://e-beta.sunat.gob.pe/ol-ti-itcpfegem-beta/billService?wsdl";
    protected $url_production="";
    protected $url_factura="";

public function __construct()
    {
        parent::__construct();
        $this->url_production=base_url().'wsdl/SunatProd.wsdl';
        $this->url_factura=$this->url_production;
    }

public function index(){

	echo  "/Factura/index". "<br>";
    $this->load->helper('file');
    $extensions = array('xml');
    $folder='xml_firmado/';
    $filenames = get_filenames($folder);
    foreach ($filenames as $file) {
        if(pathinfo($folder.$file)['extension']=='xml'){
            echo $file.'<br>';
        }
        
    }
    //var_dump($filenames);
    //echo $this->url_production;
    //$this->load->library('Feedsoap',array('url'=>$this->url_production));

    //$feedsoap = new Feedsoap();
    //$this->load->library('someclass',array('nombre'=>'jose'));
    //$this->someclass->some_method();

}

public function estado($ruc, $tipodoc, $serie, $numero){
    $method = $_SERVER['REQUEST_METHOD'];

    if($method!='GET'){
        json_output(400,array('status'=>400, 'message'=>'Bad request.'));
    }else {
        $XMLString = '<?xml version="1.0" encoding="UTF-8"?>
        <soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:ser="http://service.sunat.gob.pe" xmlns:wsse="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd">
         <soapenv:Header>
             <wsse:Security>
                 <wsse:UsernameToken>
                     <wsse:Username>'.$this->username.'</wsse:Username>
                     <wsse:Password>'.$this->password.'</wsse:Password>
                 </wsse:UsernameToken>
             </wsse:Security>
         </soapenv:Header>
         <soapenv:Body>
              <ser:getStatus>
                 <rucComprobante>'.$ruc.'</rucComprobante>
                 <tipoComprobante>'.$tipodoc.'</tipoComprobante>
                 <serieComprobante>'.$serie.'</serieComprobante>
                 <numeroComprobante>'.$numero.'</numeroComprobante>
              </ser:getStatus>
         </soapenv:Body>
        </soapenv:Envelope>';

        $params =array('url' => $this->url_consult);
        $this->load->library('Feedsoap',$params);
        $this->feedsoap->SoapClientCall($XMLString); 
        $this->feedsoap->__call("getStatus", array(), array());
        $xml =  new DOMDocument();
        $xml->loadXML($this->feedsoap->__getLastResponse());
        $code = $xml->getElementsByTagName('statusCode')->item(0)->nodeValue;
        $message = $xml->getElementsByTagName('statusMessage')->item(0)->nodeValue;

        /*
        $xpath = new DomXpath($xml);
        foreach ($xpath->query('//statusMessage') as $message){ }
        foreach ($xpath->query('//statusCode') as $code){ }*/
        json_output(200,array('status'=>'200', 'code'=>$code, 'message'=>$message));
    }
}

public function estadocdr($ruc, $tipodoc, $serie, $numero){
    $method = $_SERVER['REQUEST_METHOD'];

    if($method!='GET'){
        json_output(400,array('status'=>400, 'message'=>'Bad request.'));
    }else {
        $XMLString = '<?xml version="1.0" encoding="UTF-8"?>
        <soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:ser="http://service.sunat.gob.pe" xmlns:wsse="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd">
         <soapenv:Header>
             <wsse:Security>
                 <wsse:UsernameToken>
                     <wsse:Username>'.$this->username.'</wsse:Username>
                     <wsse:Password>'.$this->password.'</wsse:Password>
                 </wsse:UsernameToken>
             </wsse:Security>
         </soapenv:Header>
         <soapenv:Body>
              <ser:getStatusCdr>
                 <rucComprobante>'.$ruc.'</rucComprobante>
                 <tipoComprobante>'.$tipodoc.'</tipoComprobante>
                 <serieComprobante>'.$serie.'</serieComprobante>
                 <numeroComprobante>'.$numero.'</numeroComprobante>
              </ser:getStatusCdr>
         </soapenv:Body>
        </soapenv:Envelope>';

        $params =array('url' => $this->url_consult);
        $this->load->library('Feedsoap',$params);
        $this->feedsoap->SoapClientCall($XMLString); 
        $this->feedsoap->__call("getStatusCdr", array(), array());
        $xml =  new DOMDocument();
        $xml->loadXML($this->feedsoap->__getLastResponse());
        $content = $xml->getElementsByTagName('content')->item(0)->nodeValue;
        $code = $xml->getElementsByTagName('statusCode')->item(0)->nodeValue;
        $message = $xml->getElementsByTagName('statusMessage')->item(0)->nodeValue;

        $folder='xml_firmado/';
        $filename = $ruc.'-'.$tipodoc.'-'.$serie.'-'.$numero;
        $cdr=base64_decode($content);
        $archivo = fopen($folder.'R-'.$filename.'.zip','w+');
        fputs($archivo,$cdr);
        fclose($archivo);

        //DESCOMPRIMIR ARCHIVO
        /*
        $zip = new ZipArchive;
        $res = $zip->open($folder.'R-'.$filename.'.zip');

        if ($res === TRUE) {
            $zip->extractTo($folder);
            $zip->close();            
        }*/

        /*
        $xpath = new DomXpath($xml);
        foreach ($xpath->query('//statusMessage') as $message){ }
        foreach ($xpath->query('//statusCode') as $code){ }*/
        json_output(200,array(
            'status'=>'200', 
            'code'=>$code, 
            'message'=>$message,
            'content'=>$content
            ));
    }
}

public function ticket($ticket){
    
    $folder='temp/ticket/';

    $XMLString = '<?xml version="1.0" encoding="UTF-8"?>
    <soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:ser="http://service.sunat.gob.pe" xmlns:wsse="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd">
     <soapenv:Header>
         <wsse:Security>
             <wsse:UsernameToken>
                 <wsse:Username>'.$this->username.'</wsse:Username>
                 <wsse:Password>'.$this->password.'</wsse:Password>
             </wsse:UsernameToken>
         </wsse:Security>
     </soapenv:Header>
     <soapenv:Body>
         <ser:getStatus>
            <ticket>'.$ticket.'</ticket>
         </ser:getStatus>
     </soapenv:Body>
    </soapenv:Envelope>';

    $params =array('url' => $this->url_factura);
    $this->load->library('Feedsoap', $params);
    //$feedsoap = new Feedsoap();
    $this->feedsoap->SoapClientCall($XMLString); 
    $this->feedsoap->__call("getStatus", array(), array());
    $result = $this->feedsoap->__getLastResponse();

    $r_doc = new DOMDocument();
    $r_doc->loadXML($result);

    $statusCode=$r_doc->getElementsByTagName('statusCode')->item(0)->nodeValue;
    $respuesta =$r_doc->getElementsByTagName('content')->item(0)->nodeValue;
    $filename='';

    $cdr=base64_decode($respuesta);
    $archivo = fopen($folder.'R-'.$ticket.'.zip','w+');
    fputs($archivo,$cdr);
    fclose($archivo);

    //DESCOMPRIMIR ARCHIVO
    $zip = new ZipArchive;
    $res = $zip->open($folder.'R-'.$ticket.'.zip');

    if ($res === TRUE) {
        $zip->extractTo($folder);
        $zip->close();
        
        //buscar el xml
        $this->load->helper('file');
        $filenames = get_filenames($folder);
        foreach ($filenames as $file) {
            if(pathinfo($folder.$file)['extension']=='xml') $filename=$file;
        }
        if($filename!=''){
            $xml = new DOMDocument();
            $xml->load($folder.$filename);
            $id = $xml->getElementsByTagName('ReferenceID')->item(0)->nodeValue;
            $code = $xml->getElementsByTagName('ResponseCode')->item(0)->nodeValue;
            $mensaje = $xml->getElementsByTagName('Description')->item(0)->nodeValue; 
        }
        delete_files($folder);
    } 

    $arr=array(
        'statusCode'=>$statusCode,
        'id'=>$id,
        'code'=>$code,
        'mensaje'=>$mensaje
        );
    header('Content-Type: application/json');
    echo json_encode( $arr );


}

public function resumen(){
    $method = $_SERVER['REQUEST_METHOD'];

        if($method!='POST'){
            json_output(400,array('status'=>400, 'message'=>'Bad request.'));
        }else {
            $cab = json_decode(file_get_contents('php://input'), true);

        $emp_tipo_documento = $cab['emp_tipo_documento'];//6
        $ruc = $cab['emp_ruc'];
        $emp_razonsocial = $cab['emp_razonsocial'];

        $id_comunicacion =$cab['id_comunicacion'];// 'RC-20181002-001';
        $fecha_comunicacion = $cab['fecha_comunicacion'];
        $fecha_resumen = $cab['fecha_resumen'];
        

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
        $Invoice->appendChild($dom->createElement('cbc:ID',$id_comunicacion));
        $Invoice->appendChild($dom->createElement('cbc:ReferenceDate',$fecha_resumen));
        $Invoice->appendChild($dom->createElement('cbc:IssueDate',$fecha_comunicacion));

        //bloque2 cac:Signature
        $Signature = $dom->createElement('cac:Signature');
        $Invoice->appendChild($Signature);
        $Signature->appendChild($dom->createElement('cbc:ID',$id_comunicacion));
        $SignatoryParty = $dom->createElement('cac:SignatoryParty');
        $Signature->appendChild($SignatoryParty);
        $PartyIdentification = $dom->createElement('cac:PartyIdentification');
        $SignatoryParty->appendChild($PartyIdentification);
        $PartyIdentification->appendChild($dom->createElement('cbc:ID',$ruc));
        $PartyName = $dom->createElement('cac:PartyName');
        $SignatoryParty->appendChild($PartyName);
        $Name = $dom->createElement('cbc:Name',$emp_razonsocial);
        $PartyName->appendChild($Name);
        //$Name->appendChild($dom->createCDATASection("NOMBRE"));
        $DigitalSignatureAttachment = $dom->createElement('cac:DigitalSignatureAttachment');
        $Signature->appendChild($DigitalSignatureAttachment);
        $ExternalReference = $dom->createElement('cac:ExternalReference');
        $DigitalSignatureAttachment->appendChild($ExternalReference);
        $ExternalReference->appendChild($dom->createElement('cbc:URI',$ruc.'-'.$id_comunicacion));

        //bloque3 cac:AccountingSupplierParty
        $AccountingSupplierParty = $dom->createElement('cac:AccountingSupplierParty');
        $Invoice->appendChild($AccountingSupplierParty);
        $AccountingSupplierParty->appendChild($dom->createElement('cbc:CustomerAssignedAccountID',$ruc));
        $AccountingSupplierParty->appendChild($dom->createElement('cbc:AdditionalAccountID',$emp_tipo_documento));
        $Party = $dom->createElement('cac:Party');
        $AccountingSupplierParty->appendChild($Party);
        $PartyName = $dom->createElement('cac:PartyLegalEntity');
        $Party->appendChild($PartyName);
        $Name = $dom->createElement('cbc:RegistrationName',$emp_razonsocial);
        $PartyName->appendChild($Name);

        foreach ($cab['detalle'] as $item) {
            $InvoiceLine = $dom->createElement('sac:SummaryDocumentsLine');
            $Invoice->appendChild($InvoiceLine);
            $InvoiceLine->appendChild($dom->createElement('cbc:LineID',$item['orden']));
            $InvoiceLine->appendChild($dom->createElement('cbc:DocumentTypeCode',$item['tipo_documento']));
            $InvoiceLine->appendChild($dom->createElement('cbc:ID',$item['doc_numero_serie']));
            $AccountingCustomerParty = $dom->createElement('cac:AccountingCustomerParty');
                $AccountingCustomerParty->appendChild($dom->createElement('cbc:CustomerAssignedAccountID',$item['cli_numero']));
                $AccountingCustomerParty->appendChild($dom->createElement('cbc:AdditionalAccountID',$item['cli_tipo_documento']));
                $InvoiceLine->appendChild($AccountingCustomerParty);
            $Status = $dom->createElement('cac:Status');
                $Status->appendChild($dom->createElement('cbc:ConditionCode',$item['tipo_accion']));
                $InvoiceLine->appendChild($Status);
            $TotalAmount = $dom->createElement('sac:TotalAmount', $item['total']);
                $TotalAmount->setAttribute('currencyID', $item['moneda']);
                $InvoiceLine->appendChild($TotalAmount);
            $BillingPayment = $dom->createElement('sac:BillingPayment');
                $PaidAmount = $dom->createElement('cbc:PaidAmount', $item['subtotal']);
                    $PaidAmount->setAttribute('currencyID', $item['moneda']);
                    $BillingPayment->appendChild($PaidAmount);
                $BillingPayment->appendChild($dom->createElement('cbc:InstructionID','01'));

            $TaxTotal = $dom->createElement('cac:TaxTotal');
            $InvoiceLine->appendChild($TaxTotal);
            $TaxAmount = $dom->createElement('cbc:TaxAmount', $item['igv']);
            $TaxTotal->appendChild($TaxAmount);
            $TaxAmount->setAttribute('currencyID', $item['moneda']);
            $TaxSubtotal = $dom->createElement('cac:TaxSubtotal');
            $TaxTotal->appendChild($TaxSubtotal);
            $TaxAmount2 = $dom->createElement('cbc:TaxAmount', $item['igv']);
            $TaxSubtotal->appendChild($TaxAmount2);
            $TaxAmount2->setAttribute('currencyID', $item['moneda']);

            $TaxCategory = $dom->createElement('cac:TaxCategory');
            $TaxSubtotal->appendChild($TaxCategory);
            $TaxScheme = $dom->createElement('cac:TaxScheme');
            $TaxCategory->appendChild($TaxScheme);
            $TaxScheme->appendChild($dom->createElement('cbc:ID', '1000'));
            $TaxScheme->appendChild($dom->createElement('cbc:Name', 'IGV'));
            $TaxScheme->appendChild($dom->createElement('cbc:TaxTypeCode', 'VAT'));

        }
        
        $dom->formatOutput = true;
        $dom->save( 'xml/'.$ruc.'-'.$id_comunicacion.'.xml');

        //header('Content-type: text/xml');
        //echo $dom->saveXML();


        //*************************************FIRMAR
            $filename =$ruc.'-'.$id_comunicacion;

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
        
        $r_doc = new DOMDocument();
        $r_doc->loadXML($result);
        $respuesta = $r_doc->getElementsByTagName('ticket')->item(0)->nodeValue;


        $arr=array(
                'ticket'=>$respuesta);
            header('Content-Type: application/json');
            echo json_encode( $arr );
    }
}

public function anular(){
    $method = $_SERVER['REQUEST_METHOD'];

    if($method!='POST'){
        json_output(400,array('status'=>400, 'message'=>'Bad request.'));
    }else {
        $cab = json_decode(file_get_contents('php://input'), true);
    
    $emp_tipo_documento = $cab['emp_tipo_documento'];//6
    $ruc = $cab['emp_ruc'];
    $emp_razonsocial = $cab['emp_razonsocial'];

    $id_comunicacion =$cab['id_comunicacion'];// 'RA-20181002-001';
    $fecha_comunicacion = $cab['fecha_comunicacion'];
    $fecha_baja = $cab['fecha_baja'];

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
    $Invoice->appendChild($dom->createElement('cbc:ID',$id_comunicacion));
    $Invoice->appendChild($dom->createElement('cbc:ReferenceDate',$fecha_baja));
    $Invoice->appendChild($dom->createElement('cbc:IssueDate',$fecha_comunicacion));
    //$Invoice->appendChild($dom->createElement('cbc:InvoiceTypeCode','03'));
    //$Invoice->appendChild($dom->createElement('cbc:DocumentCurrencyCode','PEN'));

    //bloque2 cac:Signature
    $Signature = $dom->createElement('cac:Signature');
    $Invoice->appendChild($Signature);
    $Signature->appendChild($dom->createElement('cbc:ID',$id_comunicacion));
    $SignatoryParty = $dom->createElement('cac:SignatoryParty');
    $Signature->appendChild($SignatoryParty);
    $PartyIdentification = $dom->createElement('cac:PartyIdentification');
    $SignatoryParty->appendChild($PartyIdentification);
    $PartyIdentification->appendChild($dom->createElement('cbc:ID',$ruc));
    $PartyName = $dom->createElement('cac:PartyName');
    $SignatoryParty->appendChild($PartyName);
    $Name = $dom->createElement('cbc:Name',$emp_razonsocial);
    $PartyName->appendChild($Name);
    //$Name->appendChild($dom->createCDATASection("NOMBRE"));


    $DigitalSignatureAttachment = $dom->createElement('cac:DigitalSignatureAttachment');
    $Signature->appendChild($DigitalSignatureAttachment);
    $ExternalReference = $dom->createElement('cac:ExternalReference');
    $DigitalSignatureAttachment->appendChild($ExternalReference);
    $ExternalReference->appendChild($dom->createElement('cbc:URI',$ruc.'-'.$id_comunicacion));

    //bloque3 cac:AccountingSupplierParty
    $AccountingSupplierParty = $dom->createElement('cac:AccountingSupplierParty');
    $Invoice->appendChild($AccountingSupplierParty);
    $AccountingSupplierParty->appendChild($dom->createElement('cbc:CustomerAssignedAccountID',$ruc));
    $AccountingSupplierParty->appendChild($dom->createElement('cbc:AdditionalAccountID',$emp_tipo_documento));
    $Party = $dom->createElement('cac:Party');
    $AccountingSupplierParty->appendChild($Party);
    $PartyName = $dom->createElement('cac:PartyLegalEntity');
    $Party->appendChild($PartyName);
    $Name = $dom->createElement('cbc:RegistrationName',$emp_razonsocial);
    $PartyName->appendChild($Name);

    foreach ($cab['detalle'] as $item) {
        $InvoiceLine = $dom->createElement('sac:VoidedDocumentsLine');
        $Invoice->appendChild($InvoiceLine);
        $InvoiceLine->appendChild($dom->createElement('cbc:LineID',$item['orden']));
        $InvoiceLine->appendChild($dom->createElement('cbc:DocumentTypeCode',$item['tipo_documento']));
        $InvoiceLine->appendChild($dom->createElement('sac:DocumentSerialID',$item['doc_serie']));
        $InvoiceLine->appendChild($dom->createElement('sac:DocumentNumberID',$item['doc_numero']));
        $InvoiceLine->appendChild($dom->createElement('sac:VoidReasonDescription',$item['motivo']));//ERROR EN SISTEMA|CANCELACION
    }

    $dom->formatOutput = true;
    $dom->save( 'xml/'.$ruc.'-'.$id_comunicacion.'.xml');
    
    //*************************************FIRMAR
        $filename =$ruc.'-'.$id_comunicacion;

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

    $XMLString = '<?xml version="1.0" encoding="UTF-8"?>
    <soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:ser="http://service.sunat.gob.pe" xmlns:wsse="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd">
     <soapenv:Header>
         <wsse:Security>
             <wsse:UsernameToken>
                 <wsse:Username>'.$this->username.'</wsse:Username>
                 <wsse:Password>'.$this->password.'</wsse:Password>
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

    $params =array('url' => $this->url_factura);
    $this->load->library('Feedsoap',$params);
    //$feedsoap = new Feedsoap();
    $this->feedsoap->SoapClientCall($XMLString); 
    $this->feedsoap->__call("sendSummary", array(), array());
    $result = $this->feedsoap->__getLastResponse();
    
    $r_doc = new DOMDocument();
    $r_doc->loadXML($result);
    $respuesta = $r_doc->getElementsByTagName('ticket')->item(0)->nodeValue;

    $arr=array(
            'ticket'=>$respuesta);
        header('Content-Type: application/json');
        echo json_encode( $arr );
    }
}

public function sunat(){
	   $method = $_SERVER['REQUEST_METHOD'];

		if($method!='POST'){
			json_output(400,array('status'=>400, 'message'=>'Bad request.'));
		}
		else
		{
            $cab = json_decode(file_get_contents('php://input'), true);

            $emp_tipo_documento = $cab['emp_tipo_documento'];
            $emp_ruc = $cab['emp_ruc'];
            $emp_razonsocial = $cab['emp_razonsocial'];
            $emp_nombrecomercial = $cab['emp_nombrecomercial'];
            $emp_direccion = $cab['emp_direccion'];
            $emp_distrito = $cab['emp_distrito'];
            $emp_provincia = $cab['emp_provincia'];
            $emp_departamento = $cab['emp_departamento'];
            $emp_ubigeo = $cab['emp_ubigeo'];
            $emp_pais = $cab['emp_pais'];
            $doc_enviaws = $cab['doc_enviaws'];

            $cli_tipo_documento = $cab['cli_tipo_documento'];
            $cli_numero = $cab['cli_numero'];
            $cli_nombre = $cab['cli_nombre'];

            $doc_tipo_documento = $cab['doc_tipo_documento'];
            $doc_numero = $cab['doc_numero'];
            $doc_fecha = $cab['doc_fecha'];
            $doc_gravada = $cab['doc_gravada'];
            $doc_igv = $cab['doc_igv'];
            $doc_descuento = $cab['doc_descuento'];
            $doc_exonerada = $cab['doc_exonerada'];
            $doc_gratuita = $cab['doc_gratuita'];
            $doc_inafecta = $cab['doc_inafecta'];
            $doc_isc = $cab['doc_isc'];
            $doc_moneda = $cab['doc_moneda'];
            $doc_otros_cargos = $cab['doc_otros_cargos'];
            $doc_otros_tributos = $cab['doc_otros_tributos'];
            $doc_total = $cab['doc_total'];

            $cantidad = $cab['cantidad'];
            $igv = $cab['igv'];

            

            $dom = new DomDocument("1.0", "ISO-8859-1");
            $dom->xmlStandalone = false;
            //$dom->formatOutput = true;
            $dom->preserveWhiteSpace = false;

            $Invoice = $dom->createElement('Invoice');
            $dom->appendChild($Invoice);

            $Invoice->setAttribute('xmlns', 'urn:oasis:names:specification:ubl:schema:xsd:Invoice-2');
            $Invoice->setAttribute('xmlns:cac', 'urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2');
            $Invoice->setAttribute('xmlns:cbc', 'urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2');
            $Invoice->setAttribute('xmlns:ccts', 'urn:un:unece:uncefact:documentation:2');
            $Invoice->setAttribute('xmlns:ds', 'http://www.w3.org/2000/09/xmldsig#');
            $Invoice->setAttribute('xmlns:ext', 'urn:oasis:names:specification:ubl:schema:xsd:CommonExtensionComponents-2');
            $Invoice->setAttribute('xmlns:qdt', 'urn:oasis:names:specification:ubl:schema:xsd:QualifiedDatatypes-2');
            $Invoice->setAttribute('xmlns:sac', 'urn:sunat:names:specification:ubl:peru:schema:xsd:SunatAggregateComponents-1');
            $Invoice->setAttribute('xmlns:udt', 'urn:un:unece:uncefact:data:specification:UnqualifiedDataTypesSchemaModule:2');
            $Invoice->setAttribute('xmlns:xsi', 'http://www.w3.org/2001/XMLSchema-instance');

            $UBLExtensions = $dom->createElement('ext:UBLExtensions');
            $Invoice->appendChild($UBLExtensions);

            $UBLExtension1 = $dom->createElement('ext:UBLExtension');
            $UBLExtensions->appendChild($UBLExtension1);
            $ExtensionContent1 = $dom->createElement('ext:ExtensionContent');
            $UBLExtension1->appendChild($ExtensionContent1);

            $AdditionalInformation = $dom->createElement('sac:AdditionalInformation');
            $ExtensionContent1->appendChild($AdditionalInformation);

            if ($doc_gravada != '0.00') {
                //agrupa1 getDocu_gravada
                $AdditionalMonetaryTotal1 = $dom->createElement('sac:AdditionalMonetaryTotal');
                $AdditionalInformation->appendChild($AdditionalMonetaryTotal1);
                $AdditionalMonetaryTotal1->appendChild($dom->createElement('cbc:ID', "1001"));
                $PayableAmount1 = $dom->createElement('cbc:PayableAmount', "100.00");
                $PayableAmount1->setAttribute('currencyID', $doc_moneda);
                $AdditionalMonetaryTotal1->appendChild($PayableAmount1);
                $anticipoCero1001 = "1";
            }
            if ($doc_inafecta != '0.00') {
                //agrupa2 getDocu_inafecta
                $AdditionalMonetaryTotal2 = $dom->createElement('sac:AdditionalMonetaryTotal');
                $AdditionalInformation->appendChild($AdditionalMonetaryTotal2);
                $AdditionalMonetaryTotal2->appendChild($dom->createElement('cbc:ID', "1002"));
                $PayableAmount2 = $dom->createElement('cbc:PayableAmount', $doc_inafecta);
                $PayableAmount2->setAttribute('currencyID', $doc_moneda);
                $AdditionalMonetaryTotal2->appendChild($PayableAmount2);
                $anticipoCero1002 = "1";
            }
            if ($doc_exonerada) {
                //agrupa3 getDocu_exonerada
                $AdditionalMonetaryTotal3 = $dom->createElement('sac:AdditionalMonetaryTotal');
                $AdditionalInformation->appendChild($AdditionalMonetaryTotal3);
                $AdditionalMonetaryTotal3->appendChild($dom->createElement('cbc:ID', "1003"));
                $PayableAmount3 = $dom->createElement('cbc:PayableAmount', $doc_exonerada);
                $PayableAmount3->setAttribute('currencyID', $doc_moneda);
                $AdditionalMonetaryTotal3->appendChild($PayableAmount3);
                $anticipoCero1003 = "1";
            }
            if ($doc_gratuita) {
                //agrupa4 getDocu_gratuita
                $AdditionalMonetaryTotal4 = $dom->createElement('sac:AdditionalMonetaryTotal');
                $AdditionalInformation->appendChild($AdditionalMonetaryTotal4);
                $AdditionalMonetaryTotal4->appendChild($dom->createElement('cbc:ID', "1004"));
                $PayableAmount4 = $dom->createElement('cbc:PayableAmount', $doc_gratuita);
                $PayableAmount4->setAttribute('currencyID', $doc_moneda);
                $AdditionalMonetaryTotal4->appendChild($PayableAmount4);
            }
            if ($doc_descuento) {
                //agrupa5 getDocu_descuento
                $AdditionalMonetaryTotal5 = $dom->createElement('sac:AdditionalMonetaryTotal');
                $AdditionalInformation->appendChild($AdditionalMonetaryTotal5);
                $AdditionalMonetaryTotal5->appendChild($dom->createElement('cbc:ID', "2005"));
                $PayableAmount5 = $dom->createElement('cbc:PayableAmount', $doc_descuento);
                $PayableAmount5->setAttribute('currencyID', $doc_moneda);
                $AdditionalMonetaryTotal5->appendChild($PayableAmount5);
            }

            foreach ($cab['leyenda'] as $item) {
                $AdditionalProperty = $dom->createElement('sac:AdditionalProperty');
                $AdditionalInformation->appendChild($AdditionalProperty);
                $AdditionalProperty->appendChild($dom->createElement('cbc:ID', $item['codigo']));
                $Value = $dom->createElement('cbc:Value', $item['descripcion']);
                $AdditionalProperty->appendChild($Value);
                //$Value->appendChild($dom->createCDATASection("CIEN Y 00/100"));
            }

            //signature
            $UBLExtension2 = $dom->createElement('ext:UBLExtension');
            $UBLExtensions->appendChild($UBLExtension2);
            $ExtensionContent2 = $dom->createElement('ext:ExtensionContent', ' ');
            $UBLExtension2->appendChild($ExtensionContent2);


            //bloque 1
            $Invoice->appendChild($dom->createElement('cbc:UBLVersionID', '2.1'));
            $Invoice->appendChild($dom->createElement('cbc:CustomizationID', '2.0'));
            /* 2.1 */
            $ProfileID = $dom->createElement('cbc:ProfileID', '0101');
            $Invoice->appendChild($ProfileID);
                $ProfileID->setAttribute('schemeName', 'SUNAT:Identificador de Tipo de Operación');
                $ProfileID->setAttribute('schemeAgencyName', 'PE:SUNAT');
                $ProfileID->setAttribute('schemeURI', 'urn:pe:gob:sunat:cpe:see:gem:catalogos:catalogo17');
            /***/

            $Invoice->appendChild($dom->createElement('cbc:ID', $doc_numero));
            $Invoice->appendChild($dom->createElement('cbc:IssueDate', $doc_fecha));

            //$Invoice->appendChild($dom->createElement('cbc:InvoiceTypeCode', $doc_tipo_documento));

            /* 2.1 */
            $Invoice->appendChild($dom->createElement('cbc:IssueTime', '00:00:00'));
            $Invoice->appendChild($dom->createElement('cbc:DueDate', $doc_fecha));

            $InvoiceTypeCode = $dom->createElement('cbc:InvoiceTypeCode', '01');
            $Invoice->appendChild($InvoiceTypeCode);
                $InvoiceTypeCode->setAttribute('listAgencyName', 'PE:SUNAT');
                $InvoiceTypeCode->setAttribute('listName', 'SUNAT:Identificador de Tipo de Documento');
                $InvoiceTypeCode->setAttribute('listURI', 'urn:pe:gob:sunat:cpe:see:gem:catalogos:catalogo01');
                $InvoiceTypeCode->setAttribute('listID', '0101');
                $InvoiceTypeCode->setAttribute('name', 'Tipo de Operacion');
                $InvoiceTypeCode->setAttribute('listSchemeURI', 'urn:pe:gob:sunat:cpe:see:gem:catalogos:catalogo51');

        /*
            $Note1 = $dom->createElement('cbc:Note', 'VEINTIDOS MIL CIENTO TREINTITRES Y 80/100');
            $Invoice->appendChild($Note1);
                $Note1->setAttribute('languageLocaleID','1000');

            $Note2 = $dom->createElement('cbc:Note', 'COMPROBANTE DE PERCEPCION');
            $Invoice->appendChild($Note2);
                $Note2->setAttribute('languageLocaleID','2000');

            $Note3 = $dom->createElement('cbc:Note', '0501002017012000125');
            $Invoice->appendChild($Note3);
                $Note3->setAttribute('languageLocaleID','3000');
            */

            $DocumentCurrencyCode = $dom->createElement('cbc:DocumentCurrencyCode', $doc_moneda);
            $Invoice->appendChild($DocumentCurrencyCode);
                $DocumentCurrencyCode->setAttribute('listID', 'ISO 4217 Alpha');
                $DocumentCurrencyCode->setAttribute('listName', 'Currency');
                $DocumentCurrencyCode->setAttribute('listAgencyName', 'United Nations Economic Commission for Europe');

            $Invoice->appendChild($dom->createElement('cbc:LineCountNumeric', $cantidad));
            /***/

            //bloque2 cac:Signature
            $Signature = $dom->createElement('cac:Signature');
            $Invoice->appendChild($Signature);
                $Signature->appendChild($dom->createElement('cbc:ID', $doc_numero));
                $SignatoryParty = $dom->createElement('cac:SignatoryParty');
                $Signature->appendChild($SignatoryParty);
                    $PartyIdentification = $dom->createElement('cac:PartyIdentification');
                    $SignatoryParty->appendChild($PartyIdentification);
                        $PartyIdentification->appendChild($dom->createElement('cbc:ID', $emp_ruc));
                    $PartyName = $dom->createElement('cac:PartyName');
                    $SignatoryParty->appendChild($PartyName);
                        $Name = $dom->createElement('cbc:Name', $emp_razonsocial);
                        $PartyName->appendChild($Name);
                        //$Name->appendChild($dom->createCDATASection("NOMBRE"));
                $DigitalSignatureAttachment = $dom->createElement('cac:DigitalSignatureAttachment');
                $Signature->appendChild($DigitalSignatureAttachment);
                    $ExternalReference = $dom->createElement('cac:ExternalReference');
                    $DigitalSignatureAttachment->appendChild($ExternalReference);
                        $ExternalReference->appendChild($dom->createElement('cbc:URI', $emp_ruc));

            //bloque3 cac:AccountingSupplierParty
            $AccountingSupplierParty = $dom->createElement('cac:AccountingSupplierParty');
            $Invoice->appendChild($AccountingSupplierParty);
                $Party = $dom->createElement('cac:Party');
                $AccountingSupplierParty->appendChild($Party);                
                    $PartyIdentification = $dom->createElement('cac:PartyIdentification');
                    $Party->appendChild($PartyIdentification);
                        $ID = $dom->createElement('cbc:ID',$emp_ruc);
                        $PartyIdentification->appendChild($ID);
                            $ID->setAttribute('schemeID','6');
                            $ID->setAttribute('schemeName','Documento de Identidad');
                            $ID->setAttribute('schemeAgencyName','PE:SUNAT');
                            $ID->setAttribute('schemeURI','urn:pe:gob:sunat:cpe:see:gem:catalogos:catalogo06');
                    $PartyName = $dom->createElement('cac:PartyName');
                    $Party->appendChild($PartyName);
                        $Name = $dom->createElement('cbc:Name', $emp_razonsocial);
                        $PartyName->appendChild($Name);
                        //$Name->appendChild($dom->createCDATASection("NOMBRE"));
                    $PartyTaxScheme = $dom->createElement('cac:PartyTaxScheme');
                    $Party->appendChild($PartyTaxScheme);
                        $PartyTaxScheme->appendChild($dom->createElement('cbc:RegistrationName', $emp_razonsocial));
                        $CompanyID = $dom->createElement('cbc:CompanyID',$emp_ruc);
                        $PartyTaxScheme->appendChild($CompanyID);
                                $CompanyID->setAttribute('schemeID','6');
                                $CompanyID->setAttribute('schemeName','SUNAT:Identificador de Documento de Identidad');
                                $CompanyID->setAttribute('schemeAgencyName','PE:SUNAT');
                                $CompanyID->setAttribute('schemeURI','urn:pe:gob:sunat:cpe:see:gem:catalogos:catalogo06');
                        $TaxScheme = $dom->createElement('cac:TaxScheme');
                        $PartyTaxScheme->appendChild($TaxScheme);
                            $ID = $dom->createElement('cbc:ID',$emp_ruc);
                            $TaxScheme->appendChild($ID);
                                $ID->setAttribute('schemeID','6');
                                $ID->setAttribute('schemeName','SUNAT:Identificador de Documento de Identidad');
                                $ID->setAttribute('schemeAgencyName','PE:SUNAT');
                                $ID->setAttribute('schemeURI','urn:pe:gob:sunat:cpe:see:gem:catalogos:catalogo06');
                    $PartyLegalEntity = $dom->createElement('cac:PartyLegalEntity');
                    $Party->appendChild($PartyLegalEntity);
                        $PartyLegalEntity->appendChild($dom->createElement('cbc:RegistrationName', $emp_razonsocial));
                        $RegistrationAddress = $dom->createElement('cac:RegistrationAddress');
                        $PartyLegalEntity->appendChild($RegistrationAddress);
                            $ID = $dom->createElement('cbc:ID');
                            $RegistrationAddress->appendChild($ID);
                                $ID->setAttribute('schemeName','ubigeos');
                                $ID->setAttribute('schemeAgencyName','PE:INEI');
                            $AddressTypeCode = $dom->createElement('cbc:AddressTypeCode','0000');
                            $RegistrationAddress->appendChild($AddressTypeCode);
                                $AddressTypeCode->setAttribute('listAgencyName','PE:SUNAT');
                                $AddressTypeCode->setAttribute('listName','Establecimientos anexos');
                            $RegistrationAddress->appendChild($dom->createElement('cbc:CityName', $emp_provincia));
                            $RegistrationAddress->appendChild($dom->createElement('cbc:CountrySubentity', $emp_departamento));
                            $RegistrationAddress->appendChild($dom->createElement('cbc:District', $emp_distrito));
                            $AddressLine = $dom->createElement('cac:AddressLine');
                            $RegistrationAddress->appendChild($AddressLine);
                                $AddressLine->appendChild($dom->createElement('cbc:Line', $emp_direccion));                            
                            $Country = $dom->createElement('cac:Country');
                            $RegistrationAddress->appendChild($Country);
                                $IdentificationCode = $dom->createElement('cbc:IdentificationCode', $emp_pais);
                                $Country->appendChild($IdentificationCode);
                                    $IdentificationCode->setAttribute('listID','ISO 3166-1');
                                    $IdentificationCode->setAttribute('listAgencyName','United Nations Economic Commission for Europe');
                                    $IdentificationCode->setAttribute('listName','ICountry');
                    $Contact = $dom->createElement('cac:Contact');
                        $Contact->appendChild($dom->createElement('cbc:Name', ''));

            //bloque 4            
            $AccountingCustomerParty = $dom->createElement('cac:AccountingCustomerParty');
            $Invoice->appendChild($AccountingCustomerParty);
                $Party = $dom->createElement('cac:Party');
                $AccountingCustomerParty->appendChild($Party);                
                    $PartyIdentification = $dom->createElement('cac:PartyIdentification');
                    $Party->appendChild($PartyIdentification);
                        $ID = $dom->createElement('cbc:ID',$cli_numero);
                        $PartyIdentification->appendChild($ID);
                            $ID->setAttribute('schemeID',$cli_tipo_documento);
                            $ID->setAttribute('schemeName','Documento de Identidad');
                            $ID->setAttribute('schemeAgencyName','PE:SUNAT');
                            $ID->setAttribute('schemeURI','urn:pe:gob:sunat:cpe:see:gem:catalogos:catalogo06');
                    $PartyName = $dom->createElement('cac:PartyName');
                    $Party->appendChild($PartyName);
                        $Name = $dom->createElement('cbc:Name', $cli_nombre);
                        $PartyName->appendChild($Name);
                        //$Name->appendChild($dom->createCDATASection("NOMBRE"));
                    $PartyTaxScheme = $dom->createElement('cac:PartyTaxScheme');
                    $Party->appendChild($PartyTaxScheme);
                        $PartyTaxScheme->appendChild($dom->createElement('cbc:RegistrationName', $cli_nombre));
                        $CompanyID = $dom->createElement('cbc:CompanyID',$cli_numero);
                        $PartyTaxScheme->appendChild($CompanyID);
                                $CompanyID->setAttribute('schemeID',$cli_tipo_documento);
                                $CompanyID->setAttribute('schemeName','SUNAT:Identificador de Documento de Identidad');
                                $CompanyID->setAttribute('schemeAgencyName','PE:SUNAT');
                                $CompanyID->setAttribute('schemeURI','urn:pe:gob:sunat:cpe:see:gem:catalogos:catalogo06');
                        $TaxScheme = $dom->createElement('cac:TaxScheme');
                        $PartyTaxScheme->appendChild($TaxScheme);
                            $ID = $dom->createElement('cbc:ID',$cli_numero);
                            $TaxScheme->appendChild($ID);
                                $ID->setAttribute('schemeID',$cli_tipo_documento);
                                $ID->setAttribute('schemeName','SUNAT:Identificador de Documento de Identidad');
                                $ID->setAttribute('schemeAgencyName','PE:SUNAT');
                                $ID->setAttribute('schemeURI','urn:pe:gob:sunat:cpe:see:gem:catalogos:catalogo06');
                    $PartyLegalEntity = $dom->createElement('cac:PartyLegalEntity');
                    $Party->appendChild($PartyLegalEntity);
                        $PartyLegalEntity->appendChild($dom->createElement('cbc:RegistrationName', $cli_nombre));
                        $RegistrationAddress = $dom->createElement('cac:RegistrationAddress');
                        $PartyLegalEntity->appendChild($RegistrationAddress);
                            $ID = $dom->createElement('cbc:ID');
                            $RegistrationAddress->appendChild($ID);
                                $ID->setAttribute('schemeName','ubigeos');
                                $ID->setAttribute('schemeAgencyName','PE:INEI');
                            $RegistrationAddress->appendChild($dom->createElement('cbc:CityName', ''));
                            $RegistrationAddress->appendChild($dom->createElement('cbc:CountrySubentity',''));
                            $RegistrationAddress->appendChild($dom->createElement('cbc:District', ''));
                            $AddressLine = $dom->createElement('cac:AddressLine');
                            $RegistrationAddress->appendChild($AddressLine);
                                $AddressLine->appendChild($dom->createElement('cbc:Line', ''));                            
                            $Country = $dom->createElement('cac:Country');
                            $RegistrationAddress->appendChild($Country);
                                $IdentificationCode = $dom->createElement('cbc:IdentificationCode', 'PE');
                                $Country->appendChild($IdentificationCode);
                                    $IdentificationCode->setAttribute('listID','ISO 3166-1');
                                    $IdentificationCode->setAttribute('listAgencyName','United Nations Economic Commission for Europe');
                                    $IdentificationCode->setAttribute('listName','ICountry');


            //2.1
            $AllowanceCharge = $dom->createElement('cac:AllowanceCharge');
            $Invoice->appendChild($AllowanceCharge);
                $AllowanceCharge->appendChild($dom->createElement('cbc:ChargeIndicator', 'false'));
                $AllowanceChargeReasonCode = $dom->createElement('cbc:AllowanceChargeReasonCode','02');
                $AllowanceCharge->appendChild($AllowanceChargeReasonCode);
                    $AllowanceChargeReasonCode->setAttribute('listName','Cargo/descuento');
                    $AllowanceChargeReasonCode->setAttribute('listAgencyName','PE:SUNAT');
                    $AllowanceChargeReasonCode->setAttribute('listURI','urn:pe:gob:sunat:cpe:see:gem:catalogos:catalogo53');
                $AllowanceCharge->appendChild($dom->createElement('cbc:MultiplierFactorNumeric', 0));
                $Amount = $dom->createElement('cbc:Amount',0);
                $AllowanceCharge->appendChild($Amount);
                    $Amount->setAttribute('currencyID','PEN');
                $BaseAmount = $dom->createElement('cbc:BaseAmount',0);
                $AllowanceCharge->appendChild($BaseAmount);
                    $BaseAmount->setAttribute('currencyID','PEN');

            //bloque 5 getDocu_igv
            $TaxTotal = $dom->createElement('cac:TaxTotal');
            $Invoice->appendChild($TaxTotal);
                $TaxAmount = $dom->createElement('cbc:TaxAmount', $doc_igv);
                $TaxTotal->appendChild($TaxAmount);
                    $TaxAmount->setAttribute('currencyID', $doc_moneda);
                $TaxSubtotal = $dom->createElement('cac:TaxSubtotal');
                $TaxTotal->appendChild($TaxSubtotal);
                    $TaxableAmount = $dom->createElement('cbc:TaxableAmount', $doc_gravada);
                    $TaxSubtotal->appendChild($TaxableAmount);
                        $TaxableAmount->setAttribute('currencyID', $doc_moneda);
                    $TaxAmount2 = $dom->createElement('cbc:TaxAmount', $doc_igv);
                    $TaxSubtotal->appendChild($TaxAmount2);
                        $TaxAmount2->setAttribute('currencyID', $doc_moneda);
                    $TaxCategory = $dom->createElement('cac:TaxCategory');
                    $TaxSubtotal->appendChild($TaxCategory);
                        $ID = $dom->createElement('cbc:ID','S');
                        $TaxCategory->appendChild($ID);
                            $ID->setAttribute('schemeID','UN/ECE 5305');
                            $ID->setAttribute('schemeName','Tax Category Identifier');
                            $ID->setAttribute('schemeAgencyName','United Nations Economic Commission for Europe');                            
                        $TaxScheme = $dom->createElement('cac:TaxScheme');
                        $TaxCategory->appendChild($TaxScheme);
                        $ID = $dom->createElement('cbc:ID', '1000');
                        $TaxScheme->appendChild($ID);
                            $ID->setAttribute('schemeID','UN/ECE 5153');
                            $ID->setAttribute('schemeAgencyID','6');
                        $TaxScheme->appendChild($dom->createElement('cbc:Name', 'IGV'));
                        $TaxScheme->appendChild($dom->createElement('cbc:TaxTypeCode', 'VAT'));

            //bloque 6
            $LegalMonetaryTotal = $dom->createElement('cac:LegalMonetaryTotal');
            $Invoice->appendChild($LegalMonetaryTotal);
                $LineExtensionAmount = $dom->createElement('cbc:LineExtensionAmount', $doc_gravada);
                $LegalMonetaryTotal->appendChild($LineExtensionAmount);
                $LineExtensionAmount->setAttribute('currencyID', $doc_moneda);

                $TaxInclusiveAmount = $dom->createElement('cbc:TaxInclusiveAmount', $doc_total);
                $LegalMonetaryTotal->appendChild($TaxInclusiveAmount);
                $TaxInclusiveAmount->setAttribute('currencyID', $doc_moneda);
            //if ($doc_descuento != '0.00') {
                $AllowanceTotalAmount = $dom->createElement('cbc:AllowanceTotalAmount', 0);
                $LegalMonetaryTotal->appendChild($AllowanceTotalAmount);
                $AllowanceTotalAmount->setAttribute('currencyID', $doc_moneda);
            //}
                $ChargeTotalAmount = $dom->createElement('cbc:ChargeTotalAmount', 0);
                $LegalMonetaryTotal->appendChild($ChargeTotalAmount);
                $ChargeTotalAmount->setAttribute('currencyID', $doc_moneda);

                $PayableAmount = $dom->createElement('cbc:PayableAmount', $doc_total);
                $LegalMonetaryTotal->appendChild($PayableAmount);
                $PayableAmount->setAttribute('currencyID', $doc_moneda);

            //detalle factura
            foreach ($cab['detalle'] as $item) {

                $InvoiceLine = $dom->createElement('cac:InvoiceLine');
                $Invoice->appendChild($InvoiceLine);
                    $InvoiceLine->appendChild($dom->createElement('cbc:ID', $item['orden']));
                    $InvoicedQuantity = $dom->createElement('cbc:InvoicedQuantity', $item['cantidad']);
                    $InvoiceLine->appendChild($InvoicedQuantity);
                        $InvoicedQuantity->setAttribute('unitCode', $item['unidad']);
                        $InvoicedQuantity->setAttribute('unitCodeListID', 'UN/ECE rec 20');
                        $InvoicedQuantity->setAttribute('unitCodeListAgencyName', 'United Nations Economic Commission for Europe');

                    $LineExtensionAmount = $dom->createElement('cbc:LineExtensionAmount', $item['subtotal']);
                    $InvoiceLine->appendChild($LineExtensionAmount);
                        $LineExtensionAmount->setAttribute('currencyID', $doc_moneda);

                    $PricingReference = $dom->createElement('cac:PricingReference');
                    $InvoiceLine->appendChild($PricingReference);
                        $AlternativeConditionPrice = $dom->createElement('cac:AlternativeConditionPrice');
                        $PricingReference->appendChild($AlternativeConditionPrice);
                            $PriceAmount = $dom->createElement('cbc:PriceAmount', $item['precio']);
                            $AlternativeConditionPrice->appendChild($PriceAmount);
                                $PriceAmount->setAttribute('currencyID', $doc_moneda);
                            $PriceTypeCode = $dom->createElement('cbc:PriceTypeCode', '01');
                            $AlternativeConditionPrice->appendChild($PriceTypeCode);
                                $PriceTypeCode->setAttribute('listName','Tipo de Precio');
                                $PriceTypeCode->setAttribute('listAgencyName','PE:SUNAT');
                                $PriceTypeCode->setAttribute('listURI','urn:pe:gob:sunat:cpe:see:gem:catalogos:catalogo16');

                /*
                if ($item['precio_no_onerosa'] != '0.00') {
                    $AlternativeConditionPrice2 = $dom->createElement('cac:AlternativeConditionPrice');
                    $PricingReference->appendChild($AlternativeConditionPrice2);
                    $PriceAmount2 = $dom->createElement('cbc:PriceAmount', $item['precio_no_onerosa']);
                    $AlternativeConditionPrice2->appendChild($PriceAmount2);
                    $PriceAmount2->setAttribute('currencyID', $doc_moneda);
                    $AlternativeConditionPrice2->appendChild($dom->createElement('cbc:PriceTypeCode', '02'));
                }
                */

                $TaxTotal = $dom->createElement('cac:TaxTotal');
                $InvoiceLine->appendChild($TaxTotal);
                    $TaxAmount = $dom->createElement('cbc:TaxAmount', $item['igv']);
                    $TaxTotal->appendChild($TaxAmount);
                        $TaxAmount->setAttribute('currencyID', $doc_moneda);
                    $TaxSubtotal = $dom->createElement('cac:TaxSubtotal');
                    $TaxTotal->appendChild($TaxSubtotal);
                        $TaxableAmount = $dom->createElement('cbc:TaxableAmount', $item['subtotal']);
                        $TaxSubtotal->appendChild($TaxableAmount);
                            $TaxableAmount->setAttribute('currencyID', $doc_moneda);
                        $TaxAmount2 = $dom->createElement('cbc:TaxAmount', $item['igv']);
                        $TaxSubtotal->appendChild($TaxAmount2);
                            $TaxAmount2->setAttribute('currencyID', $doc_moneda);                
                        $TaxCategory = $dom->createElement('cac:TaxCategory');
                        $TaxSubtotal->appendChild($TaxCategory);
                            $ID = $dom->createElement('cbc:ID','S');
                            $TaxCategory->appendChild($ID);
                                $ID->setAttribute('schemeID','UN/ECE 5305');
                                $ID->setAttribute('schemeName','Tax Category Identifier');
                                $ID->setAttribute('schemeAgencyName','United Nations Economic Commission for Europe');
                            $TaxCategory->appendChild($dom->createElement('cbc:Percent', $igv));
                            $TaxExemptionReasonCode = $dom->createElement('cbc:TaxExemptionReasonCode','10');
                            $TaxCategory->appendChild($TaxExemptionReasonCode);
                                $TaxExemptionReasonCode->setAttribute('listAgencyName','PE:SUNAT');
                                $TaxExemptionReasonCode->setAttribute('listName','SUNAT:Codigo de Tipo de Afectación del IGV');
                                $TaxExemptionReasonCode->setAttribute('listURI','urn:pe:gob:sunat:cpe:see:gem:catalogos:catalogo07');
                            //$TaxCategory->appendChild($dom->createElement('cbc:TierRange', '10'));
                            $TaxScheme = $dom->createElement('cac:TaxScheme');
                            $TaxCategory->appendChild($TaxScheme);
                                $ID = $dom->createElement('cbc:ID','1000');
                                $TaxScheme->appendChild($ID);
                                    $ID->setAttribute('schemeID','UN/ECE 5305');
                                    $ID->setAttribute('schemeName','Tax Category Identifier');
                                    $ID->setAttribute('schemeAgencyName','United Nations Economic Commission for Europe');
                                $TaxScheme->appendChild($dom->createElement('cbc:Name', 'IGV'));
                                $TaxScheme->appendChild($dom->createElement('cbc:TaxTypeCode', 'VAT'));

                $Item = $dom->createElement('cac:Item');
                $InvoiceLine->appendChild($Item);
                    $Item->appendChild($dom->createElement('cbc:Description', $item['descripcion']));
                    $SellersItemIdentification = $dom->createElement('cac:SellersItemIdentification');
                    $Item->appendChild($SellersItemIdentification);
                        $SellersItemIdentification->appendChild($dom->createElement('cbc:ID', $item['codigo']));
                $Price = $dom->createElement('cac:Price');
                $InvoiceLine->appendChild($Price);
                    $PriceAmount = $dom->createElement('cbc:PriceAmount', $item['precio_igv']);
                    $Price->appendChild($PriceAmount);
                        $PriceAmount->setAttribute('currencyID', $doc_moneda);
            }


            $dom->formatOutput = true;

            $dom->save('xml/' . $emp_ruc . '-' . $doc_tipo_documento . '-' . $doc_numero . '.xml');

        }

        //*************************************FIRMAR
			$filename =$emp_ruc.'-'.$doc_tipo_documento.'-'.$doc_numero;

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
		$objDSig->appendSignature($doc->getElementsByTagName('ExtensionContent')->item(1));
		
		//$doc->formatOutput = true;

		// Guardar el XML firmado
		$doc->save('xml_firmado/'.$filename.'.xml');

        //var_dump(expression)
		$Hash=$doc->getElementsByTagName('DigestValue')->item(0)->nodeValue;

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
		//$wsdlURL = 'https://e-beta.sunat.gob.pe/ol-ti-itcpfegem-beta/billService?wsdl';

		$XMLString = '<?xml version="1.0" encoding="UTF-8"?>
		<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:ser="http://service.sunat.gob.pe" xmlns:wsse="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd">
		 <soapenv:Header>
		     <wsse:Security>
		         <wsse:UsernameToken>
		             <wsse:Username>'.$this->username.'</wsse:Username>
		             <wsse:Password>'.$this->password.'</wsse:Password>
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

        $params=array('url'=>$this->url_factura);
		$this->load->library('Feedsoap',$params);
		//$feedsoap = new Feedsoap();
		$this->feedsoap->SoapClientCall($XMLString); 
		$this->feedsoap->__call("sendBill", array(), array());
		$result = $this->feedsoap->__getLastResponse();
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
			//echo 'ok';

			$r_doc = new DOMDocument();
			$r_doc->load($folder.'R-'.$filename.'.xml');
			$respuesta = $r_doc->getElementsByTagName('ResponseCode')->item(0)->nodeValue.'|'.$r_doc->getElementsByTagName('Description')->item(0)->nodeValue;


			$arr=array(
				'hash'=>$Hash,
				'respuesta'=>$respuesta,
				'zip-xml'=>base64_encode(file_get_contents($pathZipfile)),
				'zip-cdr'=>base64_encode(file_get_contents($folder.'R-'.$filename.'.zip')));
			header('Content-Type: application/json');
	    	echo json_encode( $arr );


		} else {
			echo 'failed';
		}
		//Eliminamos el Archivo Response
		unlink($folder.'C'.$filename.'.xml');
}

public function enviar($filename){		

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

public function firmar($filename){
		//$filename='20380456444-03-F001-666';// '20380456444-03-F002-00000026';//

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
		$objDSig->appendSignature($doc->getElementsByTagName('ExtensionContent')->item(1));
		
		//$doc->formatOutput = true;

		// Guardar el XML firmado
		$doc->save('xml_firmado/'.$filename.'.xml');
}

public function generar(){
	$method = $_SERVER['REQUEST_METHOD'];

	if($method!='POST'){
		json_output(400,array('status'=>400, 'message'=>'Bad request.'));
	}else{
		$cab = json_decode(file_get_contents('php://input'), true);
		
		$emp_tipo_documento=$cab['emp_tipo_documento'];
		$emp_ruc=$cab['emp_ruc'];
		$emp_razonsocial=$cab['emp_razonsocial'];
		$emp_nombrecomercial=$cab['emp_nombrecomercial'];
		$emp_direccion=$cab['emp_direccion'];
		$emp_distrito=$cab['emp_distrito'];
		$emp_provincia=$cab['emp_provincia'];
		$emp_departamento=$cab['emp_departamento'];
		$emp_ubigeo=$cab['emp_ubigeo'];
		$emp_pais=$cab['emp_pais'];
		$doc_enviaws=$cab['doc_enviaws'];

		$cli_tipo_documento=$cab['cli_tipo_documento'];
		$cli_numero=$cab['cli_numero'];
		$cli_nombre=$cab['cli_nombre'];

		$doc_tipo_documento=$cab['doc_tipo_documento'];
		$doc_numero=$cab['doc_numero'];
		$doc_fecha=$cab['doc_fecha'];
		$doc_gravada=$cab['doc_gravada'];
		$doc_igv=$cab['doc_igv'];
		$doc_descuento=$cab['doc_descuento'];
		$doc_exonerada=$cab['doc_exonerada'];
		$doc_gratuita=$cab['doc_gratuita'];
		$doc_inafecta=$cab['doc_inafecta'];
		$doc_isc=$cab['doc_isc'];
		$doc_moneda=$cab['doc_moneda'];
		$doc_otros_cargos=$cab['doc_otros_cargos'];
		$doc_otros_tributos=$cab['doc_otros_tributos'];
		$doc_total=$cab['doc_total'];
		

		$file =$emp_ruc.'-'.$doc_tipo_documento.'-'.$doc_numero.'.xml';

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

		if($doc_gravada != '0.00'){
			//agrupa1 getDocu_gravada
			$AdditionalMonetaryTotal1 = $dom->createElement('sac:AdditionalMonetaryTotal');
			$AdditionalInformation->appendChild($AdditionalMonetaryTotal1);
			$AdditionalMonetaryTotal1->appendChild($dom->createElement('cbc:ID',"1001"));
			$PayableAmount1 = $dom->createElement('cbc:PayableAmount',"100.00");
			$PayableAmount1->setAttribute('currencyID',$doc_moneda);
			$AdditionalMonetaryTotal1->appendChild($PayableAmount1);
			$anticipoCero1001="1";
		}
		if($doc_inafecta != '0.00'){
			//agrupa2 getDocu_inafecta
			$AdditionalMonetaryTotal2 = $dom->createElement('sac:AdditionalMonetaryTotal');
			$AdditionalInformation->appendChild($AdditionalMonetaryTotal2);
			$AdditionalMonetaryTotal2->appendChild($dom->createElement('cbc:ID',"1002"));
			$PayableAmount2 = $dom->createElement('cbc:PayableAmount',$doc_inafecta);
			$PayableAmount2->setAttribute('currencyID',$doc_moneda);
			$AdditionalMonetaryTotal2->appendChild($PayableAmount2);
			$anticipoCero1002="1";
		}
		if($doc_exonerada){
			//agrupa3 getDocu_exonerada
			$AdditionalMonetaryTotal3 = $dom->createElement('sac:AdditionalMonetaryTotal');
			$AdditionalInformation->appendChild($AdditionalMonetaryTotal3);
			$AdditionalMonetaryTotal3->appendChild($dom->createElement('cbc:ID',"1003"));
			$PayableAmount3 = $dom->createElement('cbc:PayableAmount',$doc_exonerada);
			$PayableAmount3->setAttribute('currencyID',$doc_moneda);
			$AdditionalMonetaryTotal3->appendChild($PayableAmount3);
			$anticipoCero1003="1";
		}
		if($doc_gratuita){
			//agrupa4 getDocu_gratuita
			$AdditionalMonetaryTotal4 = $dom->createElement('sac:AdditionalMonetaryTotal');
			$AdditionalInformation->appendChild($AdditionalMonetaryTotal4);
			$AdditionalMonetaryTotal4->appendChild($dom->createElement('cbc:ID',"1004"));
			$PayableAmount4 = $dom->createElement('cbc:PayableAmount',$doc_gratuita);
			$PayableAmount4->setAttribute('currencyID',$doc_moneda);
			$AdditionalMonetaryTotal4->appendChild($PayableAmount4);
		}
		if($doc_descuento){
			//agrupa5 getDocu_descuento
			$AdditionalMonetaryTotal5 = $dom->createElement('sac:AdditionalMonetaryTotal');
			$AdditionalInformation->appendChild($AdditionalMonetaryTotal5);
			$AdditionalMonetaryTotal5->appendChild($dom->createElement('cbc:ID',"2005"));
			$PayableAmount5 = $dom->createElement('cbc:PayableAmount',$doc_descuento);
			$PayableAmount5->setAttribute('currencyID',$doc_moneda);
			$AdditionalMonetaryTotal5->appendChild($PayableAmount5);
		}

		foreach ($cab['leyenda'] as $item) { 
			$AdditionalProperty = $dom->createElement('sac:AdditionalProperty');
			$AdditionalInformation->appendChild($AdditionalProperty);
			$AdditionalProperty->appendChild($dom->createElement('cbc:ID',$item['codigo']));
			$Value=$dom->createElement('cbc:Value',$item['descripcion']);
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
		$Invoice->appendChild($dom->createElement('cbc:ID',$doc_numero));
		$Invoice->appendChild($dom->createElement('cbc:IssueDate',$doc_fecha));
		$Invoice->appendChild($dom->createElement('cbc:InvoiceTypeCode',$doc_tipo_documento));
		$Invoice->appendChild($dom->createElement('cbc:DocumentCurrencyCode',$doc_moneda));

		//bloque2 cac:Signature
		$Signature = $dom->createElement('cac:Signature');
		$Invoice->appendChild($Signature);
		$Signature->appendChild($dom->createElement('cbc:ID',$emp_ruc));
		$SignatoryParty = $dom->createElement('cac:SignatoryParty');
		$Signature->appendChild($SignatoryParty);
		$PartyIdentification = $dom->createElement('cac:PartyIdentification');
		$SignatoryParty->appendChild($PartyIdentification);
		$PartyIdentification->appendChild($dom->createElement('cbc:ID',$emp_ruc));
		$PartyName = $dom->createElement('cac:PartyName');
		$SignatoryParty->appendChild($PartyName);
		$Name = $dom->createElement('cbc:Name',$emp_razonsocial);
		$PartyName->appendChild($Name);
		//$Name->appendChild($dom->createCDATASection("NOMBRE"));


		$DigitalSignatureAttachment = $dom->createElement('cac:DigitalSignatureAttachment');
		$Signature->appendChild($DigitalSignatureAttachment);
		$ExternalReference = $dom->createElement('cac:ExternalReference');
		$DigitalSignatureAttachment->appendChild($ExternalReference);
		$ExternalReference->appendChild($dom->createElement('cbc:URI',$emp_ruc));

		//bloque3 cac:AccountingSupplierParty
		$AccountingSupplierParty = $dom->createElement('cac:AccountingSupplierParty');
		$Invoice->appendChild($AccountingSupplierParty);
		$AccountingSupplierParty->appendChild($dom->createElement('cbc:CustomerAssignedAccountID',$emp_ruc));
		$AccountingSupplierParty->appendChild($dom->createElement('cbc:AdditionalAccountID',$emp_tipo_documento));
		$Party = $dom->createElement('cac:Party');
		$AccountingSupplierParty->appendChild($Party);
		$PartyName = $dom->createElement('cac:PartyName');
		$Party->appendChild($PartyName);
		$Name = $dom->createElement('cbc:Name',$emp_razonsocial);
		$PartyName->appendChild($Name);
		//$Name->appendChild($dom->createCDATASection("NOMBRE"));


		$PostalAddress = $dom->createElement('cac:PostalAddress');
		$Party->appendChild($PostalAddress);
		$PostalAddress->appendChild($dom->createElement('cbc:ID',$emp_ubigeo));
		$PostalAddress->appendChild($dom->createElement('cbc:StreetName',$emp_direccion));
		$PostalAddress->appendChild($dom->createElement('cbc:CitySubdivisionName',''));
		$PostalAddress->appendChild($dom->createElement('cbc:CityName',$emp_provincia));
		$PostalAddress->appendChild($dom->createElement('cbc:CountrySubentity',$emp_departamento));
		$PostalAddress->appendChild($dom->createElement('cbc:District',$emp_distrito));
		$Country = $dom->createElement('cac:Country');
		$PostalAddress->appendChild($Country);
		$Country->appendChild($dom->createElement('cbc:IdentificationCode',$emp_pais));
		$PartyLegalEntity = $dom->createElement('cac:PartyLegalEntity');
		$Party->appendChild($PartyLegalEntity);
		$RegistrationName = $dom->createElement('cbc:RegistrationName',$emp_razonsocial);
		$PartyLegalEntity->appendChild($RegistrationName);
		//$RegistrationName->appendChild($dom->createCDATASection("NOMBRE"));
		//bloque 4
		$AccountingCustomerParty = $dom->createElement('cac:AccountingCustomerParty');
		$Invoice->appendChild($AccountingCustomerParty);
		$AccountingCustomerParty->appendChild($dom->createElement('cbc:CustomerAssignedAccountID',$cli_numero));
		$AccountingCustomerParty->appendChild($dom->createElement('cbc:AdditionalAccountID',$cli_tipo_documento));
		$Party2 = $dom->createElement('cac:Party');
		$AccountingCustomerParty->appendChild($Party2);
		$PartyLegalEntity2 = $dom->createElement('cac:PartyLegalEntity');
		$Party2->appendChild($PartyLegalEntity2);
		$PartyLegalEntity2->appendChild($dom->createElement('cbc:RegistrationName',$cli_nombre));

		//bloque 5 getDocu_igv
		$TaxTotal = $dom->createElement('cac:TaxTotal');
		$Invoice->appendChild($TaxTotal);
		$TaxAmount = $dom->createElement('cbc:TaxAmount',$doc_igv);
		$TaxTotal->appendChild($TaxAmount);
		$TaxAmount->setAttribute('currencyID',$doc_moneda);
		$TaxSubtotal = $dom->createElement('cac:TaxSubtotal');
		$TaxTotal->appendChild($TaxSubtotal);
		$TaxAmount2 = $dom->createElement('cbc:TaxAmount',$doc_igv);
		$TaxSubtotal->appendChild($TaxAmount2);
		$TaxAmount2->setAttribute('currencyID',$doc_moneda);
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
		
		if($doc_descuento != '0.00'){
			$AllowanceTotalAmount = $dom->createElement('cbc:AllowanceTotalAmount',$doc_descuento);
			$LegalMonetaryTotal->appendChild($AllowanceTotalAmount);
			$AllowanceTotalAmount->setAttribute('currencyID',$doc_moneda);
		}
		
		$PayableAmount = $dom->createElement('cbc:PayableAmount',$doc_total);
		$LegalMonetaryTotal->appendChild($PayableAmount);
		$PayableAmount->setAttribute('currencyID',$doc_moneda);

		//detalle factura
		foreach ($cab['detalle'] as $item) {
		
			$InvoiceLine = $dom->createElement('cac:InvoiceLine');
			$Invoice->appendChild($InvoiceLine);
			$InvoiceLine->appendChild($dom->createElement('cbc:ID',$item['orden']));
			$InvoicedQuantity = $dom->createElement('cbc:InvoicedQuantity',$item['cantidad']);
			$InvoiceLine->appendChild($InvoicedQuantity);
			$InvoicedQuantity->setAttribute('unitCode',$item['unidad']);

			$LineExtensionAmount = $dom->createElement('cbc:LineExtensionAmount',$item['subtotal']);
			$InvoiceLine->appendChild($LineExtensionAmount);
			$LineExtensionAmount->setAttribute('currencyID',$doc_moneda);

			$PricingReference = $dom->createElement('cac:PricingReference');
			$InvoiceLine->appendChild($PricingReference);
			$AlternativeConditionPrice = $dom->createElement('cac:AlternativeConditionPrice');
			$PricingReference->appendChild($AlternativeConditionPrice);
			$PriceAmount = $dom->createElement('cbc:PriceAmount',$item['precio']);
			$AlternativeConditionPrice->appendChild($PriceAmount);
			$PriceAmount->setAttribute('currencyID',$doc_moneda);
			$AlternativeConditionPrice->appendChild($dom->createElement('cbc:PriceTypeCode','01'));

			if($item['precio_no_onerosa'] != '0.00'){
				$AlternativeConditionPrice2 = $dom->createElement('cac:AlternativeConditionPrice');
				$PricingReference->appendChild($AlternativeConditionPrice2);
				$PriceAmount2 = $dom->createElement('cbc:PriceAmount',$item['precio_no_onerosa']);
				$AlternativeConditionPrice2->appendChild($PriceAmount2);
				$PriceAmount2->setAttribute('currencyID',$doc_moneda);
				$AlternativeConditionPrice2->appendChild($dom->createElement('cbc:PriceTypeCode','02'));
			}

			$TaxTotal = $dom->createElement('cac:TaxTotal');
			$InvoiceLine->appendChild($TaxTotal);
			$TaxAmount = $dom->createElement('cbc:TaxAmount',$item['igv']);
			$TaxTotal->appendChild($TaxAmount);
			$TaxAmount->setAttribute('currencyID',$doc_moneda);
			$TaxSubtotal = $dom->createElement('cac:TaxSubtotal');
			$TaxTotal->appendChild($TaxSubtotal);
			$TaxableAmount = $dom->createElement('cbc:TaxableAmount',$item['igv']);
			$TaxSubtotal->appendChild($TaxableAmount);
			$TaxableAmount->setAttribute('currencyID',$doc_moneda);
			$TaxAmount2 = $dom->createElement('cbc:TaxAmount',$item['igv']);
			$TaxSubtotal->appendChild($TaxAmount2);
			$TaxAmount2->setAttribute('currencyID',$doc_moneda);
			$TaxSubtotal->appendChild($dom->createElement('cbc:Percent','0.0'));

			$TaxCategory = $dom->createElement('cac:TaxCategory');
			$TaxSubtotal->appendChild($TaxCategory);
			$TaxCategory->appendChild($dom->createElement('cbc:ID','VAT'));
			$TaxCategory->appendChild($dom->createElement('cbc:TaxExemptionReasonCode',$item['afectacion']));
			$TaxCategory->appendChild($dom->createElement('cbc:TierRange','10'));
			$TaxScheme = $dom->createElement('cac:TaxScheme');
			$TaxCategory->appendChild($TaxScheme);
			$TaxScheme->appendChild($dom->createElement('cbc:ID','1000'));
			$TaxScheme->appendChild($dom->createElement('cbc:Name','IGV'));
			$TaxScheme->appendChild($dom->createElement('cbc:TaxTypeCode','VAT'));

			$Item = $dom->createElement('cac:Item');
			$InvoiceLine->appendChild($Item);
			$Item->appendChild($dom->createElement('cbc:Description',$item['descripcion']));
			$SellersItemIdentification = $dom->createElement('cac:SellersItemIdentification');
			$Item->appendChild($SellersItemIdentification);
			$SellersItemIdentification->appendChild($dom->createElement('cbc:ID',$item['codigo']));

			$Price = $dom->createElement('cac:Price');
			$InvoiceLine->appendChild($Price);
			$PriceAmount = $dom->createElement('cbc:PriceAmount',$item['precio']);
			$Price->appendChild($PriceAmount);
			$PriceAmount->setAttribute('currencyID',$doc_moneda);
		}
		
		$dom->formatOutput = true;		
		$dom->save( 'xml/'.$file);
	}
}
}

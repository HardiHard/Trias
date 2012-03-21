<?php

class IndexController extends Zend_Controller_Action
{

    /**
     * After submision of My_Form_Files, the output of this action 
     * (its view and layout) will go to the  progressFrame iframe defined
     * in index.phtml (this frame is needed for minitoring upload progress). This 
     * causes a lot of troubles as for example any exceptions or form validations
     * errors will be shown in the iframe rather than in the 'normal' window.
     *
     * So this must be considered when doing AJAX file uploads.
     *
     *
     *
     */
    public function indexAction()
    {
                
        $form = new My_Form_Files();

        if ($this->getRequest()->isPost()) {
            if ($form->isValid($_POST)) {
                
                $this->_helper->layout->disableLayout();
                $this->_helper->viewRenderer->setNoRender(true);
                                
                
                if (!$form->files->receive()) {
                    throw new Zend_File_Transfer_Exception('Reciving files failed');
                }               
                
                $uploadedFilesPaths = $form->files->getFileName();
                
                if(empty($uploadedFilesPaths)) {
                     $this->view->message = "No files uploaded";
                     return $this->render('finish');
                }
                
                // single uploaded file will not be an array. So make it to array.
                if (!is_array($uploadedFilesPaths)) {                    
                    $uploadedFilesPaths = (array) $uploadedFilesPaths;
                }
                
                /*
                // because this is only a demo so immidiately remove the files
                foreach ($uploadedFilesPaths as $file) {
                    if (!unlink($file)) {
                        throw new Exception('Cannot remove file: ' . $file);
                    }                    
                }      
                 */
                
                // everything went fine so go to success action  
                // this script is executed inside the iframe.
                echo '<script>window.top.location.href = "'.$this->view->baseUrl().'/index/success'.'";</script>';               
                exit;
                
            } 
        }
        
        $form->setAction($this->view->baseUrl('/index/index'));
        
        $this->view->maxUploadFileSize = ini_get('upload_max_filesize');
        $this->view->postMaxSize = ini_get('post_max_size');
        
        $this->view->form = $form;

	// Are there Files in the Upload Container ?
	$pathName = UPLOAD_PATH;
	
	if($pathName){
	    $dir = new DirectoryIterator($pathName);
	    $TemplateListArr = array(); 
	    foreach($dir as $fileInfo){
	    if($fileInfo->isDot()){
	    }else{
		//echo $fileInfo->__toString().'<br>';
		array_push($TemplateListArr, $fileInfo->__toString());
	    }
	    $this->view->templateList = $TemplateListArr;
	}
	}

    }

    public function progressAction()
    {

        // check if a request is an AJAX request
        if (!$this->getRequest()->isXmlHttpRequest()) {
            throw new Zend_Controller_Request_Exception('Not an AJAX request detected');
        }

        $uploadId = $this->getRequest()->getParam('id');

        // this is the function that actually reads the status of uploading
        $data = uploadprogress_get_info($uploadId);

        $bytesTotal = $bytesUploaded = 0;

        if (null !== $data) {
            $bytesTotal = $data['bytes_total'];
            $bytesUploaded = $data['bytes_uploaded'];
        }

        $adapter = new Zend_ProgressBar_Adapter_JsPull();
        $progressBar = new Zend_ProgressBar($adapter, 0, $bytesTotal, 'uploadProgress');

        if ($bytesTotal === $bytesUploaded) {
            $progressBar->finish();
        } else {
            $progressBar->update($bytesUploaded);
        }
    }

    public function successAction()
    {
	//action body
    }
    
    public function progresstemplateAction()
    {
    	$invoices = new Application_Model_DbTable_TriasTab();
    	$this->Invoice = $invoices->fetchRow(1);
	// progress Template to create the dummy of the Invoice 
		
    	//echo UPLOAD_PATH."/template";
    	
    	$mailMerge = new Zend_Service_LiveDocx_MailMerge();
    	$mailMerge->setUsername('HardiHard')->setPassword('postaria');
    	$mailMerge->setLocalTemplate(UPLOAD_PATH.'/invoice_template.docx');
    	
    		// Assign -> Search for software MailMerge Variable and paste meine eigene Software into
    	$mailMerge->assign('phone', $this->Invoice->phone);
    	$mailMerge->assign('date', $this->Invoice->date);
    	$mailMerge->assign('customer_number', $this->Invoice->customer_number);
    	$mailMerge->assign('invoice_number', $this->Invoice->invoice_number);
    	$mailMerge->assign('account_number', $this->Invoice->account_number);
    	$mailMerge->assign('month', $this->Invoice->month);
    	$mailMerge->assign('total_net', $this->Invoice->total_net);
    	$mailMerge->assign('tax', $this->Invoice->tax);
    	$mailMerge->assign('tax_value', $this->Invoice->tax_value);
    	$mailMerge->assign('total', $this->Invoice->total);

    	
    	//Alternatives
    	//$trias_values = array('phone','date','customer_number');
    	// $mailMerge->setFieldValues($trias_values);
    	//$trias_blockFielsValues = array('','','');
    	//$trias_blockName = "Items";
    	// $mailMerge->setBlockFieldValues($blockName, $blockFieldValues)
    	
    	
    	
    	$mailMerge->createDocument();
    	$document = $mailMerge->retrieveDocument('pdf');
    	
    	file_put_contents(UPLOAD_PATH.'/' . rand() . '.pdf', $document);
    	 
    }
	
    

    public function templatesuccessAction()
    {
        // action body
       
    }


}






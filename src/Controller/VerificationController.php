<?php
declare(strict_types=1);

namespace App\Controller;

use BaconQrCode\Renderer\Image\ImagickImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use PragmaRX\Google2FA\Google2FA;

/**
 * Verification Controller
 *
 * @method \App\Model\Entity\Verification[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */
class VerificationController extends AppController
{
    public function initialize(): void {
        parent::initialize();
        if($this->Authentication->getResult()->isValid()){
            $this->userData = $this->Authentication->getIdentity()->getOriginalData();
        }
    }

    /**
     * Index method
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function index()
    {
        $google2Fa = new Google2FA();

        $qrUrl = $google2Fa->getQRCodeUrl(
            'Idiem',
            $this->userData->getEmail(),
            $this->userData->getSecret()
        );

        $writer = new Writer(
            new ImageRenderer(
                new RendererStyle(200),
                new ImagickImageBackEnd()
            )
        );

        $imageQr = base64_encode($writer->writeString($qrUrl));

        if($this->request->is('post')){
            $validToken = $google2Fa->verifyKey(
                $this->userData->getSecret(),
                $this->request->getData('token')
            );
            if($validToken){
                $this->request->getSession()->delete('2fa_needed');
                return $this->redirect(['controller' => 'Users', 'action' => 'index']);
            }

            $this->Flash->error('Token inválido');
        }

        $this->set(compact('imageQr'));
    }

    /**
     * View method
     *
     * @param string|null $id Verification id.
     * @return \Cake\Http\Response|null|void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($id = null)
    {
        $verification = $this->Verification->get($id, [
            'contain' => [],
        ]);

        $this->set(compact('verification'));
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null|void Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $verification = $this->Verification->newEmptyEntity();
        if ($this->request->is('post')) {
            $verification = $this->Verification->patchEntity($verification, $this->request->getData());
            if ($this->Verification->save($verification)) {
                $this->Flash->success(__('The verification has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The verification could not be saved. Please, try again.'));
        }
        $this->set(compact('verification'));
    }

    /**
     * Edit method
     *
     * @param string|null $id Verification id.
     * @return \Cake\Http\Response|null|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $verification = $this->Verification->get($id, [
            'contain' => [],
        ]);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $verification = $this->Verification->patchEntity($verification, $this->request->getData());
            if ($this->Verification->save($verification)) {
                $this->Flash->success(__('The verification has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The verification could not be saved. Please, try again.'));
        }
        $this->set(compact('verification'));
    }

    /**
     * Delete method
     *
     * @param string|null $id Verification id.
     * @return \Cake\Http\Response|null|void Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $verification = $this->Verification->get($id);
        if ($this->Verification->delete($verification)) {
            $this->Flash->success(__('The verification has been deleted.'));
        } else {
            $this->Flash->error(__('The verification could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }
}

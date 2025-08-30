<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\ContractTemplate;

class ContractTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        ContractTemplate::create([
            'name' => 'Contrato de Prestação de Serviços Educacionais',
            'description' => 'Template padrão para contratos de matrícula escolar',
            'content' => $this->getContractContent(),
            'available_variables' => ContractTemplate::getSystemVariables(),
            'is_active' => true,
            'is_default' => true,
            'validity_days' => 30,
        ]);
    }

    private function getContractContent()
    {
        return '
        <div style="font-family: Arial, sans-serif; max-width: 800px; margin: 0 auto; padding: 20px;">
            <div style="text-align: center; margin-bottom: 30px;">
                <h1 style="color: #2c3e50; margin-bottom: 10px;">{{school_name}}</h1>
                <h2 style="color: #34495e; font-size: 18px;">CONTRATO DE PRESTAÇÃO DE SERVIÇOS EDUCACIONAIS</h2>
                <p style="color: #7f8c8d;">Contrato Nº: <strong>{{enrollment_number}}</strong></p>
            </div>

            <div style="margin-bottom: 25px;">
                <h3 style="color: #2c3e50; border-bottom: 2px solid #3498db; padding-bottom: 5px;">DADOS DO CONTRATANTE</h3>
                <table style="width: 100%; border-collapse: collapse;">
                    <tr>
                        <td style="padding: 5px 0; width: 30%;"><strong>Nome:</strong></td>
                        <td style="padding: 5px 0;">{{student_name}}</td>
                    </tr>
                    <tr>
                        <td style="padding: 5px 0;"><strong>CPF:</strong></td>
                        <td style="padding: 5px 0;">{{student_cpf}}</td>
                    </tr>
                    <tr>
                        <td style="padding: 5px 0;"><strong>RG:</strong></td>
                        <td style="padding: 5px 0;">{{student_rg}}</td>
                    </tr>
                    <tr>
                        <td style="padding: 5px 0;"><strong>Data de Nascimento:</strong></td>
                        <td style="padding: 5px 0;">{{student_birth_date}}</td>
                    </tr>
                    <tr>
                        <td style="padding: 5px 0;"><strong>Estado Civil:</strong></td>
                        <td style="padding: 5px 0;">{{student_civil_status}}</td>
                    </tr>
                    <tr>
                        <td style="padding: 5px 0;"><strong>Nacionalidade:</strong></td>
                        <td style="padding: 5px 0;">{{student_nationality}}</td>
                    </tr>
                    <tr>
                        <td style="padding: 5px 0;"><strong>Endereço:</strong></td>
                        <td style="padding: 5px 0;">{{student_address}}</td>
                    </tr>
                    <tr>
                        <td style="padding: 5px 0;"><strong>Telefone:</strong></td>
                        <td style="padding: 5px 0;">{{student_phone}}</td>
                    </tr>
                    <tr>
                        <td style="padding: 5px 0;"><strong>E-mail:</strong></td>
                        <td style="padding: 5px 0;">{{student_email}}</td>
                    </tr>
                    <tr>
                        <td style="padding: 5px 0;"><strong>Nome da Mãe:</strong></td>
                        <td style="padding: 5px 0;">{{student_mother_name}}</td>
                    </tr>
                    <tr>
                        <td style="padding: 5px 0;"><strong>Nome do Pai:</strong></td>
                        <td style="padding: 5px 0;">{{student_father_name}}</td>
                    </tr>
                </table>
            </div>

            <div style="margin-bottom: 25px;">
                <h3 style="color: #2c3e50; border-bottom: 2px solid #3498db; padding-bottom: 5px;">DADOS DO CURSO</h3>
                <table style="width: 100%; border-collapse: collapse;">
                    <tr>
                        <td style="padding: 5px 0; width: 30%;"><strong>Curso:</strong></td>
                        <td style="padding: 5px 0;">{{course_name}}</td>
                    </tr>
                    <tr>
                        <td style="padding: 5px 0;"><strong>Modalidade:</strong></td>
                        <td style="padding: 5px 0;">{{course_modality}}</td>
                    </tr>
                    <tr>
                        <td style="padding: 5px 0;"><strong>Turno:</strong></td>
                        <td style="padding: 5px 0;">{{course_shift}}</td>
                    </tr>
                    <tr>
                        <td style="padding: 5px 0;"><strong>Data da Matrícula:</strong></td>
                        <td style="padding: 5px 0;">{{enrollment_date}}</td>
                    </tr>
                </table>
            </div>

            <div style="margin-bottom: 25px;">
                <h3 style="color: #2c3e50; border-bottom: 2px solid #3498db; padding-bottom: 5px;">DADOS FINANCEIROS</h3>
                <table style="width: 100%; border-collapse: collapse;">
                    <tr>
                        <td style="padding: 5px 0; width: 30%;"><strong>Valor da Matrícula:</strong></td>
                        <td style="padding: 5px 0;">{{enrollment_value}}</td>
                    </tr>
                    <tr>
                        <td style="padding: 5px 0;"><strong>Valor da Mensalidade:</strong></td>
                        <td style="padding: 5px 0;">{{tuition_value}}</td>
                    </tr>
                    <tr>
                        <td style="padding: 5px 0;"><strong>Vencimento:</strong></td>
                        <td style="padding: 5px 0;">Todo dia {{due_date}} de cada mês</td>
                    </tr>
                    <tr>
                        <td style="padding: 5px 0;"><strong>Forma de Pagamento:</strong></td>
                        <td style="padding: 5px 0;">{{payment_method}}</td>
                    </tr>
                </table>
            </div>

            <div style="margin-bottom: 25px;">
                <h3 style="color: #2c3e50; border-bottom: 2px solid #3498db; padding-bottom: 5px;">CLÁUSULAS CONTRATUAIS</h3>
                
                <div style="margin-bottom: 15px;">
                    <h4 style="color: #34495e;">CLÁUSULA 1ª - DO OBJETO</h4>
                    <p style="text-align: justify; line-height: 1.6;">
                        O presente contrato tem por objeto a prestação de serviços educacionais pela CONTRATADA ao CONTRATANTE, 
                        referente ao curso de {{course_name}}, na modalidade {{course_modality}}, turno {{course_shift}}.
                    </p>
                </div>

                <div style="margin-bottom: 15px;">
                    <h4 style="color: #34495e;">CLÁUSULA 2ª - DA DURAÇÃO</h4>
                    <p style="text-align: justify; line-height: 1.6;">
                        O presente contrato vigorará pelo período correspondente ao ano letivo de {{current_year}}, 
                        podendo ser renovado mediante acordo entre as partes.
                    </p>
                </div>

                <div style="margin-bottom: 15px;">
                    <h4 style="color: #34495e;">CLÁUSULA 3ª - DO VALOR E FORMA DE PAGAMENTO</h4>
                    <p style="text-align: justify; line-height: 1.6;">
                        O valor da matrícula é de {{enrollment_value}} e o valor da mensalidade é de {{tuition_value}}, 
                        com vencimento todo dia {{due_date}} de cada mês. O pagamento deverá ser efetuado através de {{payment_method}}.
                    </p>
                </div>

                <div style="margin-bottom: 15px;">
                    <h4 style="color: #34495e;">CLÁUSULA 4ª - DAS OBRIGAÇÕES DA CONTRATADA</h4>
                    <p style="text-align: justify; line-height: 1.6;">
                        A CONTRATADA obriga-se a:
                    </p>
                    <ul style="line-height: 1.6;">
                        <li>Ministrar o ensino conforme a legislação educacional vigente;</li>
                        <li>Fornecer corpo docente qualificado;</li>
                        <li>Disponibilizar infraestrutura adequada para o ensino;</li>
                        <li>Emitir documentos escolares conforme solicitado;</li>
                        <li>Cumprir o calendário escolar estabelecido.</li>
                    </ul>
                </div>

                <div style="margin-bottom: 15px;">
                    <h4 style="color: #34495e;">CLÁUSULA 5ª - DAS OBRIGAÇÕES DO CONTRATANTE</h4>
                    <p style="text-align: justify; line-height: 1.6;">
                        O CONTRATANTE obriga-se a:
                    </p>
                    <ul style="line-height: 1.6;">
                        <li>Efetuar o pagamento das mensalidades nas datas estabelecidas;</li>
                        <li>Cumprir o regulamento interno da instituição;</li>
                        <li>Frequentar regularmente as aulas;</li>
                        <li>Comunicar qualquer alteração de dados pessoais;</li>
                        <li>Respeitar professores, funcionários e demais alunos.</li>
                    </ul>
                </div>

                <div style="margin-bottom: 15px;">
                    <h4 style="color: #34495e;">CLÁUSULA 6ª - DA RESCISÃO</h4>
                    <p style="text-align: justify; line-height: 1.6;">
                        O presente contrato poderá ser rescindido por qualquer das partes, mediante comunicação prévia 
                        de 30 (trinta) dias, sem prejuízo das obrigações já assumidas.
                    </p>
                </div>

                <div style="margin-bottom: 15px;">
                    <h4 style="color: #34495e;">CLÁUSULA 7ª - DO FORO</h4>
                    <p style="text-align: justify; line-height: 1.6;">
                        Fica eleito o foro da comarca onde está situada a sede da CONTRATADA para dirimir 
                        quaisquer questões oriundas do presente contrato.
                    </p>
                </div>
            </div>

            <div style="margin-top: 40px;">
                <p style="text-align: center; margin-bottom: 30px;">
                    Por estarem justas e contratadas, as partes assinam o presente contrato em duas vias de igual teor.
                </p>
                
                <p style="text-align: center; margin-bottom: 40px;">
                    <strong>{{current_date}}</strong>
                </p>

                <div style="display: flex; justify-content: space-between; margin-top: 60px;">
                    <div style="text-align: center; width: 45%;">
                        <div style="border-top: 1px solid #000; padding-top: 5px;">
                            <strong>{{school_name}}</strong><br>
                            <small>CONTRATADA</small>
                        </div>
                    </div>
                    
                    <div style="text-align: center; width: 45%;">
                        <div style="border-top: 1px solid #000; padding-top: 5px;">
                            <strong>{{student_name}}</strong><br>
                            <small>CONTRATANTE</small><br>
                            <small>CPF: {{student_cpf}}</small>
                        </div>
                    </div>
                </div>
            </div>

            <div style="margin-top: 40px; padding: 15px; background-color: #f8f9fa; border-radius: 5px;">
                <p style="text-align: center; font-size: 12px; color: #6c757d; margin: 0;">
                    <strong>Contrato Digital com Validade Jurídica</strong><br>
                    Este documento foi assinado digitalmente em {{contract_date}} e possui validade jurídica 
                    conforme Lei nº 14.063/2020 e MP nº 2.200-2/2001.
                </p>
            </div>
        </div>';
    }
}

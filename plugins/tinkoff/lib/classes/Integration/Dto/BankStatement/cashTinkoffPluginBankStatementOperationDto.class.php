<?php

final class cashTinkoffPluginBankStatementOperationDto implements cashTinkoffPluginToArrayInterface
{
    /**
     * Номер документа
     *
     * @var string
     */
    private $id;

    /**
     * Дата документа
     *
     * @var DateTimeImmutable
     */
    private $date;

    /**
     * Сумма платежа
     *
     * @var float
     */
    private $amount;

    /**
     * Дата списания средств с р/с плательщика
     *
     * @var DateTimeImmutable
     */
    private $drawDate;

    /**
     * Имя плательщика
     *
     * @var string
     */
    private $payerName;

    /**
     * ИНН плательщика
     *
     * @var string|null
     */
    private $payerInn;

    /**
     * Номер счета плательщика
     *
     * @var string|null
     */
    private $payerAccount;

    /**
     * Кор.счет плательщика
     *
     * @var string|null
     */
    private $payerCorrAccount;

    /**
     * БИК плательщика
     *
     * @var string
     */
    private $payerBic;

    /**
     * Банк плательщика
     *
     * @var string
     */
    private $payerBank;

    /**
     * Дата поступления средств на р/с получателя
     *
     * @var DateTimeImmutable
     */
    private $chargeDate;

    /**
     * Получатель платежа
     *
     * @var string
     */
    private $recipient;

    /**
     * ИНН получателя платежа
     *
     * @var string|null
     */
    private $recipientInn;

    /**
     * Номер счета получателя платежа
     *
     * @var string
     */
    private $recipientAccount;

    /**
     * Кор.счет получателя платежа
     *
     * @var string|null
     */
    private $recipientCorrAccount;

    /**
     * БИК получателя платежа
     *
     * @var string
     */
    private $recipientBic;

    /**
     * Банк получателя платежа
     *
     * @var string
     */
    private $recipientBank;

    /**
     * Вид платежа
     *
     * @var string|null
     */
    private $paymentType;

    /**
     * Условное обозначение (шифр) документа, проводимого по счету в кредитной организации
     *
     * @var int
     */
    private $operationType;

    /**
     * Уникальный идентификатор платежа
     *
     * @var string|null
     */
    private $uin;

    /**
     * Назначение платежа
     *
     * @var string
     */
    private $paymentPurpose;

    /**
     * Статус составителя расчетного документа
     *
     * @var string
     */
    private $creatorStatus;

    /**
     * КПП плательщика
     *
     * @var string|null
     */
    private $payerKpp;

    /**
     * КПП получателя
     *
     * @var string|null
     */
    private $recipientKpp;

    /**
     * Код бюджетной классификации
     *
     * @var string|null
     */
    private $kbk;

    /**
     * Код ОКТМО территории, на которой мобилизуются денежные средства от уплаты налога, сбора и иного платежа
     *
     * @var string|null
     */
    private $oktmo;

    /**
     * Основание налогового платежа
     *
     * @var string|null
     */
    private $taxEvidence;

    /**
     * Налоговый период / код таможенного органа
     *
     * @var string|null
     */
    private $taxPeriod;

    /**
     * Номер налогового документа
     *
     * @var string|null
     */
    private $taxDocNumber;

    /**
     * Дата налогового документа
     *
     * @var string|null
     */
    private $taxDocDate;

    /**
     * Тип налогового платежа
     *
     * @var string|null
     */
    private $taxType;

    /**
     * Очередность платежа
     *
     * @var string|null
     */
    private $executionOrder;

    public function __construct(
        string $id,
        DateTimeImmutable $date,
        float $amount,
        DateTimeImmutable $drawDate,
        string $payerName,
        ?string $payerInn,
        ?string $payerAccount,
        ?string $payerCorrAccount,
        string $payerBic,
        string $payerBank,
        DateTimeImmutable $chargeDate,
        string $recipient,
        ?string $recipientInn,
        string $recipientAccount,
        ?string $recipientCorrAccount,
        string $recipientBic,
        string $recipientBank,
        ?string $paymentType,
        int $operationType,
        ?string $uin,
        string $paymentPurpose,
        string $creatorStatus,
        ?string $payerKpp,
        ?string $recipientKpp,
        ?string $kbk,
        ?string $oktmo,
        ?string $taxEvidence,
        ?string $taxPeriod,
        ?string $taxDocNumber,
        ?string $taxDocDate,
        ?string $taxType,
        ?string $executionOrder
    ) {
        $this->id = $id;
        $this->date = $date;
        $this->amount = $amount;
        $this->drawDate = $drawDate;
        $this->payerName = $payerName;
        $this->payerInn = $payerInn;
        $this->payerAccount = $payerAccount;
        $this->payerCorrAccount = $payerCorrAccount;
        $this->payerBic = $payerBic;
        $this->payerBank = $payerBank;
        $this->chargeDate = $chargeDate;
        $this->recipient = $recipient;
        $this->recipientInn = $recipientInn;
        $this->recipientAccount = $recipientAccount;
        $this->recipientCorrAccount = $recipientCorrAccount;
        $this->recipientBic = $recipientBic;
        $this->recipientBank = $recipientBank;
        $this->paymentType = $paymentType;
        $this->operationType = $operationType;
        $this->uin = $uin;
        $this->paymentPurpose = $paymentPurpose;
        $this->creatorStatus = $creatorStatus;
        $this->payerKpp = $payerKpp;
        $this->recipientKpp = $recipientKpp;
        $this->kbk = $kbk;
        $this->oktmo = $oktmo;
        $this->taxEvidence = $taxEvidence;
        $this->taxPeriod = $taxPeriod;
        $this->taxDocNumber = $taxDocNumber;
        $this->taxDocDate = $taxDocDate;
        $this->taxType = $taxType;
        $this->executionOrder = $executionOrder;
    }

    public static function fromArray(array $data): cashTinkoffPluginBankStatementOperationDto
    {
        return new self(
            $data['id'],
            DateTimeImmutable::createFromFormat('Y-m-d|', $data['date']),
            (float) $data['amount'],
            DateTimeImmutable::createFromFormat('Y-m-d|', $data['drawDate']),
            $data['payerName'],
            $data['payerInn'] ?? null,
            $data['payerAccount'] ?? null,
            $data['payerCorrAccount'] ?? null,
            $data['payerBic'],
            $data['payerBank'],
            DateTimeImmutable::createFromFormat('Y-m-d|', $data['chargeDate']),
            $data['recipient'],
            $data['recipientInn'] ?? null,
            $data['recipientAccount'],
            $data['recipientCorrAccount'] ?? null,
            $data['recipientBic'],
            $data['recipientBank'],
            $data['paymentType'] ?? null,
            $data['operationType'],
            $data['uin'] ?? null,
            $data['paymentPurpose'],
            $data['creatorStatus'],
            $data['payerKpp'] ?? null,
            $data['recipientKpp'] ?? null,
            $data['kbk'] ?? null,
            $data['oktmo'] ?? null,
            $data['taxEvidence'] ?? null,
            $data['taxPeriod'] ?? null,
            $data['taxDocNumber'] ?? null,
            $data['taxDocDate'] ?? null,
            $data['taxType'] ?? null,
            $data['executionOrder'] ?? null
        );
    }

    /**
     * @return array<cashTinkoffPluginBankStatementOperationDto>
     */
    public static function collectionFromArray(array $data): array
    {
        $operations = [];
        foreach ($data as $datum) {
            $operations[] = self::fromArray($datum);
        }

        return $operations;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getDate(): DateTimeImmutable
    {
        return $this->date;
    }

    public function getAmount(): float
    {
        return $this->amount;
    }

    public function getDrawDate(): DateTimeImmutable
    {
        return $this->drawDate;
    }

    public function getPayerName(): string
    {
        return $this->payerName;
    }

    public function getPayerInn(): ?string
    {
        return $this->payerInn;
    }

    public function getPayerAccount(): ?string
    {
        return $this->payerAccount;
    }

    public function getPayerCorrAccount(): ?string
    {
        return $this->payerCorrAccount;
    }

    public function getPayerBic(): string
    {
        return $this->payerBic;
    }

    public function getPayerBank(): string
    {
        return $this->payerBank;
    }

    public function getChargeDate(): DateTimeImmutable
    {
        return $this->chargeDate;
    }

    public function getRecipient(): string
    {
        return $this->recipient;
    }

    public function getRecipientInn(): ?string
    {
        return $this->recipientInn;
    }

    public function getRecipientAccount(): string
    {
        return $this->recipientAccount;
    }

    public function getRecipientCorrAccount(): ?string
    {
        return $this->recipientCorrAccount;
    }

    public function getRecipientBic(): string
    {
        return $this->recipientBic;
    }

    public function getRecipientBank(): string
    {
        return $this->recipientBank;
    }

    public function getPaymentType(): ?string
    {
        return $this->paymentType;
    }

    public function getOperationType(): int
    {
        return $this->operationType;
    }

    public function getUin(): ?string
    {
        return $this->uin;
    }

    public function getPaymentPurpose(): string
    {
        return $this->paymentPurpose;
    }

    public function getCreatorStatus(): string
    {
        return $this->creatorStatus;
    }

    public function getPayerKpp(): ?string
    {
        return $this->payerKpp;
    }

    public function getRecipientKpp(): ?string
    {
        return $this->recipientKpp;
    }

    public function getKbk(): ?string
    {
        return $this->kbk;
    }

    public function getOktmo(): ?string
    {
        return $this->oktmo;
    }

    public function getTaxEvidence(): ?string
    {
        return $this->taxEvidence;
    }

    public function getTaxPeriod(): ?string
    {
        return $this->taxPeriod;
    }

    public function getTaxDocNumber(): ?string
    {
        return $this->taxDocNumber;
    }

    public function getTaxDocDate(): ?string
    {
        return $this->taxDocDate;
    }

    public function getTaxType(): ?string
    {
        return $this->taxType;
    }

    public function getExecutionOrder(): ?string
    {
        return $this->executionOrder;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'date' => $this->date,
            'amount' => $this->amount,
            'drawDate' => $this->drawDate->format('Y-m-d'),
            'payerName' => $this->payerName,
            'payerInn' => $this->payerInn,
            'payerAccount' => $this->payerAccount,
            'payerCorrAccount' => $this->payerCorrAccount,
            'payerBic' => $this->payerBic,
            'payerBank' => $this->payerBank,
            'chargeDate' => $this->chargeDate->format('Y-m-d'),
            'recipient' => $this->recipient,
            'recipientInn' => $this->recipientInn,
            'recipientAccount' => $this->recipientAccount,
            'recipientCorrAccount' => $this->recipientCorrAccount,
            'recipientBic' => $this->recipientBic,
            'recipientBank' => $this->recipientBank,
            'paymentType' => $this->paymentType,
            'operationType' => $this->operationType,
            'uin' => $this->uin,
            'paymentPurpose' => $this->paymentPurpose,
            'creatorStatus' => $this->creatorStatus,
            'payerKpp' => $this->payerKpp,
            'recipientKpp' => $this->recipientKpp,
            'kbk' => $this->kbk,
            'oktmo' => $this->oktmo,
            'taxEvidence' => $this->taxEvidence,
            'taxPeriod' => $this->taxPeriod,
            'taxDocNumber' => $this->taxDocNumber,
            'taxDocDate' => $this->taxDocDate,
            'taxType' => $this->taxType,
            'executionOrder' => $this->executionOrder,
        ];
    }
}
